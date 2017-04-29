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
                var new_packet = {};
                for (var i = 0; i < packetFields.length; i++) {
                    if(packet.hasOwnProperty(packetFields[i].layer)){
                        new_packet[packetFields[i].layer] = {};
                        for (var j = 0; j < packetFields[i].fields.length; j++) {
                            if(packet[packetFields[i].layer].hasOwnProperty(packetFields[i].fields[j])){
                                new_packet[packetFields[i].layer][ packetFields[i].fields[j] ] = packet[packetFields[i].layer][ packetFields[i].fields[j] ];
                                console.log(new_packet[packetFields[i].layer]);
                            }
                        }
                    }
                }
                new_packet.network.dstip = getPortRecipient(device.id, 0);
                sendPacket(device.id, 0, new_packet);
            } else { //replace src ip with device IP and save in NAT table
                var new_packet = {};
                console.log(packet);
                for (var i = 0; i < packetFields.length; i++) {
                    if(packet.hasOwnProperty(packetFields[i].layer)){
                        new_packet[packetFields[i].layer] = {};
                        for (var j = 0; j < packetFields[i].fields.length; j++) {
                            if(packet[packetFields[i].layer].hasOwnProperty(packetFields[i].fields[j])){
                                new_packet[packetFields[i].layer][ packetFields[i].fields[j] ] = packet[packetFields[i].layer][ packetFields[i].fields[j] ];
                            }
                        }
                    }
                }
                new_packet.network.srcip = device.id;
                sendPacket(device.id, 1, new_packet);
            }

        }
    }


        
}

