var deviceScripts = {
	manualRouter: {
		onPacketReceived: function(device, packet) {
			for (var i = 0; i < device.rules.length; i++) {
				if (device.rules[i].dstip == packet.network.dstip) {
					sendPacket(device.id, device.rules[i].portNum, packet);
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
                    if(packet.hasOwnProperty("proto")){
                        if(packet.transport.proto == "ICMP"){
                            var new_packet = {
                                network: {
                                    srcip: packet.network.dstip,
                                    dstip: packet.network.srcip
                                },
                                transport: {
                                    proto: "ICMP"
                                }
                            };
                            sendPacket(device.id, 0, new_packet);
                        }
                    }
		}
    },
    modem: {
        onPacketReceived: function(device, packet) {
            if(packet.network.dstip == device.id){//look up ip in NAT table
                var new_packet = {
                    network: {
                        srcip: packet.network.srcip,
                        dstip: getPortRecipient(device.id, 0)
                    }
                };
                sendPacket(device.id, 0, new_packet);
            } else { //replace src ip with device IP and save in NAT table
                var new_packet = {
                    network: {
                        srcip: device.id,
                        dstip: packet.network.dstip
                    }
                };
                sendPacket(device.id, 1, new_packet);
            }

        }
    }


        
}

