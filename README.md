# SimpleAlarm
Simple alarm monitor for Mitel PBXes is a simple web based application to provide visual status of the state of your PBXes.
It checkes the state of your PBX using SNMP and updates a MySQL database with any changes to that state. It can also email
alerts on these changes. 
The website will show the current state on the main page but you can drill down into each PBX to view the chistory of it. There is 
a perl script that can be scheduled to run in Windows task scheduler or Linux CRON that polls the PBX for the alarm state.

If you wihs to be a part of this project please let me know!
