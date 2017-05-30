
function onDeviceClick() {
	var ip = this.hasOwnProperty("secret") && this.secret ? "<i>secret</i>" : this.id;
	$("#subpane").html("<h3>Device info</h3><p><img src=\"./includes/"+(this.image||'imac')+".png\"></p><p>IP address: "+ip+"</p>");
	$("#subpane").show();
	$("#subpane_close").show();
	$("#leveldescrip").hide();
}

function onPacketClick() {
	var str = "<h3>Packet info</h3>";
	str += onPacketClick_helper("network", this);
	str += onPacketClick_helper("transport", this);
	str += onPacketClick_helper("application", this);

	$("#subpane").html(str);
	$("#subpane").show();
	$("#subpane_close").show();
	$("#leveldescrip").hide();
}

function onPacketClick_helper(layer, pkt) {
	if (!pkt.hasOwnProperty(layer)) return "";
	
	var keys = Object.keys(pkt[layer]);
	var str = "<h4>"+layer+" layer</h4><table>";
	for (var i = 0; i < keys.length; i++) str += "<tr><td>"+keys[i]+":</td><td>"+pkt[layer][keys[i]]+"</td></tr>";
	return str+"</table>";
}

function onSubpaneClose() {
	$("#subpane").hide();
	$("#subpane_close").hide();
	$("#leveldescrip").show();
}

function btnReset() {
	grpPackets.callAll('kill');
	grpPackets.destroy(true);
	grpPackets = game.add.group();
	game.time.reset();
	if (game.time.slowMotion == 1) btnFast();
	else btnPlay();
	initEvents();
}

function btnPause() {
	game.paused = true;
	pause_.visible = false;
	play_.visible = true;
	fast_.visible = true;
}

function btnPlay() {
	game.time.slowMotion = DEFAULT_GAMESPEED;
	game.paused = false;
	pause_.visible = true;
	play_.visible = false;
	fast_.visible = true;
}

function btnFast() {
	game.time.slowMotion = 1;
	game.paused = false;
	pause_.visible = true;
	play_.visible = true;
	fast_.visible = false;
}

function btnAdd() {
	createPacketEditor(-1, {});
}

function btnEdit() {
	createPacketEditor(this.launcherIndex, playerPackets[this.launcherIndex]);
}

function btnLaunch() {
	var pkt = playerPackets[this.launcherIndex];
	if (devices[pkt.from].locked) return;
	devices[pkt.from].locked = true;

	if (pkt.hasOwnProperty("repeat") && pkt.repeat > 1) {
		for (var i = 0; i < pkt.repeat; i++) {
			game.time.events.add( 100 * i, playPacket, pkt );
		}

		game.time.events.add(100 * (parseInt(pkt.repeat) + 1), launcherUnlock, pkt);
	} else {
		doPacketAnimation(pkt.from, getDefaultRecipient(pkt.from), pkt.payload);
		game.time.events.add(100, launcherUnlock, pkt);
	}
}

function launcherUnlock(index) {
	devices[this.from].locked = false;
}

