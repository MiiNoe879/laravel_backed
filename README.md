# BurkaWatch Backend
Laravel backend for BurkaWatch mobile app(s)

### Backend tables:
- users: has all user accounts. primary key = normalized phone number. No password, just SMS-OTP.
- images: keep track of uploaded images: user, desc, date, location, S3 path
- reports: when users report an image for non-compliance. Has imageID, reason, date, reporting user.

### Backend endpoints:

Related to users: 
- register/login (almost the same since there is no password). device gets token to identify after correct SMS code is given.
- users edit (allowed to change nickname only)

Related to images:
- get S3 upload signature: so app can directly POST to S3 bucket.
- add new image (maybe same step as S3 signature)
- edit own image

Related to reports:
- add new report. No other functionality yet. We may remove reported images automatically in the future.

<h1>Installation</h1>
composer install<br>
php artisan migrate<br>
php artisan serve<br>

- update timezone to server's<br>
config/app.php<br>
'timezone' => 'Europe/Bucharest',<br>
//http://php.net/manual/en/timezones.php
<br>
<h1>API</h1>
<h2>Signup</h2>
URL : http://localhost:8000/api/user/signup<br>
METHOD : POST<br>
REQUEST : phonenumber<br>
RESPONSE example : <br>
{
    "status": "ok",
    "status_code": 200
}

<h2>Login</h2>
URL : http://localhost:8000/api/user/login<br>
METHOD : POST<br>
REQUEST : pincode<br>
RESPONSE example :<br>
{
    "status": "ok",
    "token" : "",
    "status_code": 200
}

<h2>Update User Information</h2>
URL : http://localhost:8000/api/user/update<br>
METHOD : PUT<br>
REQUEST : nickname, token<br>
RESPONSE example : <br>
{
    "status": "ok",
    "status_code": 200
}

<h2>Report</h2>
URL : http://localhost:8000/api/report?token=<br>
METHOD : POST<br>
REQUEST : reason, image_id, token<br>
RESPONSE example : <br>
{
    "status": "ok",
    "status_code": 200
}

<h2>Image Upload</h2>
URL : http://localhost:8000/api/image<br>
METHOD : POST<br>
REQUEST : description, location, token<br>
RESPONSE example : <br>
{
    "status": "ok",
    "status_code": 200
}

<h2>Get Image List</h2>
URL : http://localhost:8000/api/image<br>
METHOD : GET<br>
REQUEST : token<br>
RESPONSE example : <br>
{
    "status": "ok",
    "status_code": 200
}

