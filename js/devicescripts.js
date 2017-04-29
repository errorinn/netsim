var deviceScripts = {
	manualRouter: {
		onPacketReceived: function(device, packet) {
			for (var i = 0; i < device.rules.length; i++) {
				if (device.rules[i].dstip == packet.network.dstip) {
					doPacketAnimation(device.id, device.ports[device.rules[i].portNum], packet);
				}
			}
		}
	},

	hub: {
		onPacketReceived: function (device, packet) {
			//.
		}
	}
}

