#!/bin/sh
printf "Content-Type: text/plain\n\n"

# Get the physical device name for the 'wan' interface
WAN_IFACE=$(ubus call network.interface.wan status | jsonfilter -e '@.l3_device')

if [ -n "$WAN_IFACE" ]; then
	# Read the RX bytes count from the sysfs entry
	RX_BYTES=$(cat /sys/class/net/"$WAN_IFACE"/statistics/rx_bytes)
	#echo "WAN RX Bytes: $RX_BYTES"
	TX_BYTES=$(cat /sys/class/net/"$WAN_IFACE"/statistics/tx_bytes)
	#echo "WAN TX Bytes: $TX_BYTES"
else
	echo "Could not find WAN physical interface."
fi

echo "{"
echo "\"rx_bytes\": \"$RX_BYTES\","
echo "\"tx_bytes\": \"$TX_BYTES\""
echo "}"
exit 0
