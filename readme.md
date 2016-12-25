##AppleTimeslotsUpdater
Ever tried to book a repair for an Apple device and local reservation slots have always been booked up?

Now you can monitor and log changes of time slot availability for Apple retail stores or local Apple authorised service providers. If you're using Chrome.

###Usage
1. Navigate to Bring in for Repair support page on Apple's web site and sign in with Apple ID
2. Search for a location of choice
3. Bring up the developers console (right click on web page > Inspect) and navigate to the Network tab
4. Assuming that you have a list if locations on the left and a map on the right, click on a location of choice
5. Scroll down the Networking tab and find or filter for the last entry that says "/web/v2/takein"
5. Right click on it > Copy as cURL
6. Paste in script between both EOF characters
7. Save & run

![Alt text](https://i.snag.gy/sOJwbQ.jpg "Screenshot")