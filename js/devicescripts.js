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
                        new_packet.srcip = packet.dstip;
                        new_packet.dstip = packet.srcip;
                        console.log(new_packet);
                        doPacketAnimation(device.id, device.ports[0], new_packet);
                    }
		}
    },
    modem: {
        onPacketReceived: function(device, packet) {
        }
    }


        
}

