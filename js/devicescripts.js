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
	},

    ping: {
		onPacketReceived: function(device, packet) {
                    if(packet.transport.proto == "ICMP"){
                        var new_packet = packet;
                        new_packet.network.srcip = packet.dstip;
                        new_packet.network.dstip = packet.srcip;
                        doPacketAnimation(device.id, device.ports[0], new_packet);
                    }
		}
    },
    modem: {
        onPacketReceived: function(device, packet) {
        }
    }


        
}

