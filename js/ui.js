$(document).ready(function(){
	/* todo: this seems to cause noticeable lag but would be really nice to have
	$(window).on('resize orientationChange', function(e) {
		//game.scale.setGameSize($(window).width(), $(window).height());
		$('#pane').css('left', ($(window).width() * 0.7) + 'px');
		$('#pane').css('width', ($(window).width() * 0.3 - 40) + 'px');
		$('#pane').css('height', ($(window).height() - 40) + 'px');
	});*/
	$('[name="username"]').focus();
	$('[type="button"]').button();
});

function sendPacket(src, portnum, payload) {
	doPacketAnimation(src, getPortRecipient(src, portnum), payload);
}

function doPacketAnimation(src, dst, payload) {
	var pkt = grpPackets.create(devices[src].sprite.centerX - 16, devices[src].sprite.centerY - 16, 'packet');
	pkt.inputEnabled = true;
	pkt.events.onInputDown.add(onPacketClick, payload);
	var tween = game.add.tween(pkt);
	pkt.dst = dst;
	pkt.payload = payload;
	pkt.portNum = getRemotePort(src, dst);
	tween.to({x: devices[dst].sprite.centerX - 16, y: devices[dst].sprite.centerY - 16}, 3000, Phaser.Easing.Sinusoidal.InOut);
	tween.onComplete.add(donePacket, pkt);
	tween.start();
}

function createLaunchers() {
	grpLaunchers.callAll('kill');
	grpLaunchers.destroy(true);
	grpLaunchers = game.add.group();

	for (var i = 0; i < playerPackets.length; i++) {
		var edit = grpLaunchers.add( game.add.button(20, 120 + i * 60, 'edit', btnEdit) );
		edit.launcherIndex = i;
		var launch = grpLaunchers.add( game.add.button(80, 120 + i * 60, 'launch', btnLaunch) );
		launch.launcherIndex = i;
	}

	grpLaunchers.add( game.add.button(20, 135 + 60 * playerPackets.length, 'add', btnAdd) );
}

function createPacketEditor(index, packet) {
	var str = "Sent from: <select id=\"pktFrom\">";
	for (var i = 0; i < level.devices.length; i++) {
		if (level.devices[i].player) {
			str += "<option"+(packet.from == level.devices[i].id ? " selected" : "")+">"+level.devices[i].id+"</option>";
		}
	}
	str += "</select><br>";

	for (var i = 0; i < packetFields.length; i++) {
		str += "<fieldset><legend>"+packetFields[i].layer+"</legend>";
		for (var j = 0; j < packetFields[i].fields.length; j++) {
			str += packetFields[i].fields[j]+":<br><input type=\"text\" id=\""+packetFields[i].layer+"_"+packetFields[i].fields[j]+"\" value=\""+payloadStr(packet, packetFields[i].layer, packetFields[i].fields[j])+"\"><br>";
		}
		str += "</fieldset>";
	}

	str += "<p>Repeat: <input type=\"text\" id=\"repeat\" style=\"width:40px;\" value=\""+(packet.hasOwnProperty("repeat") ? packet.repeat : 1)+"\"></p>";

	$("#editor").html(str);
	$('#editor').dialog({
		title: index < 0 ? "Add packet" : "Update packet",
		resizable:false,
		buttons:[
			{ text: "Remove", click:function() { deletePlayerPacket(index); createLaunchers(); $(this).dialog("close"); }},
			{ text: index < 0 ? "Add" : "Update", click:function() { updatePlayerPacket(index < 0 ? playerPackets.length : index); createLaunchers(); $(this).dialog("close");}}
		]
	});
	$('select').selectmenu();
	$('#editor').show();
}

function deletePlayerPacket(index) {
	playerPackets.splice(index, 1);
	savePlayerPackets();
}

function updatePlayerPacket(index) {
	playerPackets[index] = {
		from: $("#pktFrom").val(),
		repeat: $("#repeat").val(),
		payload:{}
	};

	for (var i = 0; i < packetFields.length; i++) {
		playerPackets[index].payload[packetFields[i].layer] = {};
		for (var j = 0; j < packetFields[i].fields.length; j++) {
			var val = $("#"+packetFields[i].layer+"_"+packetFields[i].fields[j]).val();
			if (val != "") {
				playerPackets[index].payload[packetFields[i].layer][ packetFields[i].fields[j] ] = val;
			}
		}
	}

	savePlayerPackets();
}

function payloadStr(packet, layer, field) {
	return packet.hasOwnProperty("payload") && packet.payload.hasOwnProperty(layer) && packet.payload[layer].hasOwnProperty(field) ? packet.payload[layer][field] : "";
}

function savePlayerPackets() {
	$.post("./solns.ajax.php?level="+levelid+"&method=save", {
		json:JSON.stringify(playerPackets)
	});
}

function loadPlayerPackets() {
	$.getJSON("./solns.ajax.php?level="+levelid+"&method=load").done(function(data){
		playerPackets = data;
		createLaunchers();
	}).fail(function(jxr, txt, err) {
		console.log("lPP fail: "+txt+", "+err);
	});
}

