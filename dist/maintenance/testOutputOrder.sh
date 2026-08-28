#!/bin/bash
echo "Standard out 1"
echo "Standard error 1" 1>&2
sleep 20s
echo "Standard out 2"
echo "Standard error 2" 1>&2
