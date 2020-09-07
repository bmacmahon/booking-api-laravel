#!/bin/bash

echo 'Get providers'
curl 'http://chrono.test.com/api/providers' 

echo 'Get availabilities'
curl 'http://chrono.test.com/api/availabilities/GREGORYHOUSE/2020-09-10/2020-09-20'

echo 'Book'
curl -d 'providername=GREGORYHOUSE&patientname=JOHNSMITH&start_datetime=2020-09-10+08:45:00' http://chrono.test.com/api/appointment