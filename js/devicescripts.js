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
                    if(packet.hasOwnProperty("transport") && packet["transport"].hasOwnProperty("proto")){
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
                            }
                        }
                    }
                }
                new_packet.network.dstip = getPortRecipient(device.id, 0);
                sendPacket(device.id, 0, new_packet);
            } else { //replace src ip with device IP and save in NAT table
                var new_packet = {};
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
    },
    switch: {
        onPacketReceived: function(device, packet, portNum) {
            var found = false;
            for (var i = 0; (i < device.rules.length) && !found; i++) {
                if (device.rules[i].dstip == packet.network.dstip) {
                    sendPacket(device.id, device.rules[i].portNum, packet);
                    found = true;
                }
            }
            if(!found){
                //broadcast packet to all ports except where it was received
                for(var i=0; i<device.ports.length; i++){
                    if(i != portNum){
                        sendPacket(device.id, i, packet);
                    }
                }
            }
            //update rules with info from this packet
            var found = false;
            for (var i = 0; (i < device.rules.length) && !found; i++) {
                if (device.rules[i].dstip == packet.network.srcip) {
                    device.rules[i].portNum = portNum;
                    found = true;
                }
            }
            if(!found){
                device.rules[device.rules.length] = {
                    dstip: packet.network.srcip,
                    portNum: portNum
                }
            }
        }
    },
    firewall: {
        onPacketReceived: function(device, packet) {

            function checkRules(rule){
                return rule.srcip == packet.network.srcip;
            }

            if(device.rules.find(checkRules) == undefined){
                sendPacket(device.id, 0, packet);
            }

        }
    },
    broadcast: {
        onPacketReceived: function(device, packet, portNum){
            function checkRules(rule){
                return rule.dstip == packet.network.dstip;
            }

            var rule = device.rules.find(checkRules);
            if (rule != undefined){
                sendPacket(device.id, rule.portNum, packet);
            } else {
                if(packet.network.dstip == "Broadcast"){
                    for(var i=0; i<device.ports.length; i++){
                        if((i != portNum) && (getPortRecipient(device.id, i) != "Google")){
                            sendPacket(device.id, i, packet);
                        }
                    }
                }
            }
        }
    },
    encryption: {
        onPacketReceived: function(device, packet, portNum) {
            if(packet.hasOwnProperty("transport") && packet["transport"].hasOwnProperty("proto")){
                if(packet.transport.proto == "encryption"){
                    if(packet.hasOwnProperty("application"]) && packet["application"].hasOwnProperty("type")){
                    var type = packet.application.payload;

                    switch(type) {
                        case "keyrequest": 
                            var new_packet = {
                                network: {
                                    srcip: packet.network.dstip,
                                    dstip: packet.network.srcip
                                },
                                transport: {
                                    proto: "encryption"
                                },
                                application: {
                                    type: "keyresponse",
                                    payload: "123456"
                                }
                            }
                            sendPacket(device.id, portNum, new_packet);
                        break;
                        case "keyresponse":
                            var new_packet = {
                                network: {
                                    srcip: packet.network.dstip,
                                    dstip: packet.network.srcip
                                },
                                transport: {
                                    proto: "encryption"
                                },
                                application: {
                                    type: "message"
                                }
                            }
                            break;
                        default:
                            break;
                    }
                    }
                }
            }
        }
                            
    }                        
    
}

