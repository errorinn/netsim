var DEFAULT_GAMESPEED = 3;

var levelid = <?=$leveldata['id']?>;
var level = <?php include 'levels/'.$leveldata['filename'].'.json'; ?>;
var devices = {};
var playerPackets = [];

var packetFields = [
	{layer:"network", fields:[
		"srcip", "dstip"
	]},
	{layer:"transport", fields:[
		"proto", "ttl"
	]},
        {layer:"application", fields:[
                "type", "key"
	]}
];

var vpWidth = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
var vpHeight = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;

var game = new Phaser.Game(vpWidth, vpHeight, Phaser.AUTO, 'game', { preload: preload, create: create, update: update });
var grpPackets;
var grpDevices;
var grpLaunchers;
var pause, pause_, play, play_, fast, fast_;

function preload() {
	game.load.image('imac', 'includes/imac.png');
	game.load.image('iphone-1', 'includes/iphone-1.png');
	game.load.image('macbook', 'includes/macbook.png');
	game.load.image('monitor', 'includes/monitor.png');
	game.load.image('packet', 'includes/circle.png');
	game.load.image('server', 'includes/server.png');
	game.load.image('router', 'includes/router.png');

	game.load.image('reset', 'includes/ui/reset.png');
	game.load.image('pause', 'includes/ui/pause.png');
	game.load.image('pause_', 'includes/ui/pause_grey.png');
	game.load.image('play', 'includes/ui/play.png');
	game.load.image('play_', 'includes/ui/play_grey.png');
	game.load.image('fast', 'includes/ui/fast.png');
	game.load.image('fast_', 'includes/ui/fast_grey.png');
	game.load.image('edit', 'includes/ui/tabs.png');
	game.load.image('launch', 'includes/ui/launch.png');
	game.load.image('add', 'includes/ui/add.png');

	for (var i = 0; i <= 6; i++) game.load.image('meter-'+i, 'includes/ui/meter-'+i+'.png');
}

function create() {
	game.stage.backgroundColor = 0xDDDDDD;
	grpDevices = game.add.group();
	grpPackets = game.add.group();
	grpLaunchers = game.add.group();
	document.getElementById('pane').style.left = (vpWidth * 0.7) + 'px';
	document.getElementById('pane').style.width = (vpWidth * 0.3 - 40) + 'px';
	document.getElementById('pane').style.height = (vpHeight - 40) + 'px';

<?php if (LOGGEDIN) { ?>
	pause = game.add.sprite(80, 20, 'pause');
	play = game.add.sprite(140, 20, 'play');
	fast = game.add.sprite(200, 20, 'fast');

	game.add.button(20, 20, 'reset', btnReset);
	pause_ = game.add.button(80, 20, 'pause_', btnPause);
	play_ = game.add.button(140, 20, 'play_', btnPlay);
	fast_ = game.add.button(200, 20, 'fast_', btnFast);
	createLaunchers();

	fast_.visible = false;
<?php } ?>

	for (var i = 0; i < level.devices.length; i++) {
		var devSprite = grpDevices.create(0.7 * game.world.width * level.devices[i].x, game.world.height * level.devices[i].y, level.devices[i].image || 'imac');
		level.devices[i].sprite = devSprite;
		if (level.devices[i].hasOwnProperty("capacity")) {
			level.devices[i].capsprite = grpDevices.create(0.7 * game.world.width * level.devices[i].x + 128,   game.world.height * level.devices[i].y, 'meter-0');
		}
		devices[level.devices[i].id] = level.devices[i];
		devices[level.devices[i].id].ports = [];
		devices[level.devices[i].id].locked = false;
		devSprite.inputEnabled = true;
		devSprite.events.onInputDown.add(onDeviceClick, level.devices[i]);
	}

	var graphics = game.add.graphics(0,0);
	graphics.lineStyle(1, 0, 0);
	graphics.lineTo(1,1);
	graphics.lineStyle(1, 0x000000, 1);

	for (var i = 0; i < level.links.length; i++) {
		var src = devices[level.links[i].src];
		var dst = devices[level.links[i].dst];
		src.ports[ level.links[i].srcport ] = dst.id;
		dst.ports[ level.links[i].dstport ] = src.id;
		graphics.moveTo(src.sprite.centerX, src.sprite.centerY);
		graphics.lineTo(dst.sprite.centerX, dst.sprite.centerY);
	}

	var meshSprite = game.add.sprite(0, 0, graphics.generateTexture());
	meshSprite.sendToBack();
	graphics.destroy();

	if (!level.hasOwnProperty("triggers")) level.triggers = [];

	$("#loading").hide();

<?php if (LOGGEDIN) { ?>
	game.input.keyboard.onPressCallback = function(e){ if (e == " ") {
		if (game.paused) {
			if (game.time.slowMotion == 1) btnFast();
			else btnPlay();
		} else btnPause();
	}};
	loadPlayerPackets();
	btnReset();
<?php } else { ?>
	$("input[type=submit").button();
	initEvents();
<?php } ?>
}

function initEvents() {
	for (var i = 0; i < level.timeline.length; i++) {
		game.time.events.add(level.timeline[i].at * 3, playPacket, level.timeline[i]);
	}
}

function playPacket() {
	doPacketAnimation(this.from, getDefaultRecipient(this.from), this.payload);
}

function getDefaultRecipient(from) {
	for (var i = 0; i < level.links.length; i++) {
		if (level.links[i].src == from) return level.links[i].dst;
		else if (level.links[i].dst == from) return level.links[i].src;
	}
	return null;
}

function getPortRecipient(from, portNum) {
	for (var i = 0; i < level.links.length; i++) {
		if (level.links[i].src == from && level.links[i].srcport == portNum) return level.links[i].dst;
		if (level.links[i].dst == from && level.links[i].dstport == portNum) return level.links[i].src;
	}
	return null;

}

// WARNING: this should only be called by the animator
// devicescripts should not be able to access it
function getRemotePort(src, dst) {
	for (var i = 0; i < level.links.length; i++) {
		if (level.links[i].src == src && level.links[i].dst == dst) return level.links[i].dstport;
		if (level.links[i].src == dst && level.links[i].dst == src) return level.links[i].srcport;
	}
}

function update() {
	//todo: separate out the meter-X updates from satisfiesTrigger
	for (var i = 0; i < level.triggers.length; i++) {
		if (level.triggers[i].type == "flood") {
			satisfiesTrigger({dst: level.triggers[i].device}, {type:"flood",device:level.triggers[i].device,noup:true});
		}
	}
}

var levelOver = false;
function donePacket() {
	this.kill();
	var youWin = true;

	for (var i = 0; i < level.triggers.length; i++) {
		if (satisfiesTrigger(this, level.triggers[i])) {
			if (level.triggers[i].hasOwnProperty("times")) {
				if (--level.triggers[i].times <= 0) level.triggers[i].completed = true;
			} else level.triggers[i].completed = true;
		}

		if (!level.triggers[i].hasOwnProperty("completed")) youWin = false;
	}

	if (!levelOver && youWin) {
		levelOver = true;
		$.get("./solns.ajax.php?level="+levelid+"&method=win");
		$("#winner").dialog({
			title:"You win!",
			resizable:false,
			modal:true,
			buttons:[{text:"Go to the next level", click:function(){ location.href="./?level="+level.nextLevel; }}]
		});
	}

	if (devices[this.dst].hasOwnProperty("script")) {
		devices[this.dst].script.onPacketReceived(devices[this.dst], this.payload, this.portNum);
	}
}

function satisfiesTrigger(pkt, t) {
	if (pkt.dst != t.device) return false;
	
	if (t.type == "packet") {
		if (!t.hasOwnProperty("payload") && !t.hasOwnProperty("times")) return true;
		if (!pkt.hasOwnProperty("payload")) return false;
	
		var layers = t.hasOwnProperty("payload") ? Object.keys(t.payload) : [];
		for (var i = 0; i < layers.length; i++) {
			if (!pkt.payload.hasOwnProperty(layers[i])) return false;
	
			var fields = Object.keys(t.payload[ layers[i] ]);
			for (var j = 0; j < fields.length; j++) {
				if (!pkt.payload[ layers[i] ].hasOwnProperty(fields[j])) return false;
				if (pkt.payload[ layers[i] ][ fields[j] ].trim().toLowerCase() != t.payload[ layers[i] ][ fields[j] ].trim().toLowerCase()) return false;
			}
		}

		return true;
	} else if (t.type == "flood") {
		if (!devices[t.device].hasOwnProperty("floodCounter")) {
			devices[t.device].floodCounter = 0;
			devices[t.device].floodLast = 0;
		}

		var delta = game.time.events.ms - devices[t.device].floodLast;
		if (t.noup && devices[t.device].floodCounter > 0) {
			if (delta > 200) {
				devices[t.device].floodCounter--;
				devices[t.device].floodLast = game.time.events.ms;
			}
		} else if (delta < 120 / devices[t.device].capacity) {
			devices[t.device].floodCounter++;
			if (devices[t.device].floodCounter > 30) devices[t.device].floodCounter = 30;
		} else {
			devices[t.device].floodCounter -= Math.floor(delta / (120 / devices[t.device].capacity));
			if (devices[t.device].floodCounter < 0) devices[t.device].floodCounter = 0;
		}

		if (!t.noup) devices[t.device].floodLast = game.time.events.ms;
		devices[t.device].capsprite.loadTexture('meter-'+Math.floor( devices[t.device].floodCounter / 5 ));

		return devices[t.device].floodCounter == 30;
	} else {
		console.log("unknown trigger type: "+t.type);
		return false;
	}
}

