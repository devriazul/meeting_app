# Meeting App

# Api calls

localhost can be replaced by other web domains. This is a generic outline

## api test
http://localhost:8000/api/auth/hello (for local use)

http://ec2-13-58-238-37.us-east-2.compute.amazonaws.com  

http://ec2-13-58-238-37.us-east-2.compute.amazonaws.com/api/auth/hello  


# Input requirements
->All values besides the room and strings must be double digits  
->User token will expire after 20 minutes and the user must log in again


## SignUp
->Outputs status okay, with token user must use.  
Required:  
-name  
-email  
-password

    -http://localhost:8000/api/auth/signup (POST)


## Login
->Outputs status okay, with credentials, and token that user must use.  
Required:  
-email  
-password

    -http://localhost:8000/api/auth/login (POST)


## Save meeting
->Returns message "meeting booked", status code, and meeting information.  
Required:  
-room  
-month  
-day  
-hour  
-minute  
-hour_finish  
-minute_finish

    -http://localhost:8000/api/meeting/store?token={user_token} (POST)


## Edit meeting
->Returns message meeting edited on success.  
->Enter the ID of the meeting that should be changed with the new room, date, and time.  
  
Required:  
-{id} -> enter into url  
-room  
-month  
-day  
-hour  
-minute  
-hour_finish  
-minute_finish

    -http://localhost:8000/api/meeting/edit/{id}?token={user_token} (PUT)


## Delete room
->Returns message "meeting deleted" on success.  
Enter the ID of the meeting that should be deleted  

Required:  
-{id} -> enter into url

    -http://localhost:8000/api/meeting/delete/{id}?token={user_token} (DELETE)


## Get rooms
->Returns a list of the rooms available  

    -http://localhost:8000/api/meeting/retrieve/rooms?token={user_token} (GET)


## Get meetings
->Returns a list of all the meetings that are scheduled  

    -http://localhost:8000/api/meeting/retrieve/meetings?token={user_token} (GET)


## Get meeting based on room with date range
->Returns a list of the meetings that are scheduled for a room between the dates ranges that are set.  
  
Required:  
-room -> to be queried  
-month  
-day  
-hour  
-minute  
-month_finish  
-day_finish  
-hour_finish  
-minute_finish

    -http://localhost:8000/api/meeting/retrieve/meeting/room?token={user_token} (POST)
       

## Get meetings based on user with date range
->Returns a list of the meetings that are scheduled for a user between the dates ranges that are set.  
  
Required:  
-email -> of the user to be queried  
-month  
-day  
-hour  
-minute  
-month_finish
-day_finish  
-hour_finish  
-minute_finish

    -http://localhost:8000/api/meeting/retrieve/meeting/user?token={user_token} (POST)

## Invite a user
->Returns message "okay, status code 200, and Ivite data.  
Required:  
-meeting_id -> id of the meeting  
-invitee_email -> email of the user to be invited  

    -http://localhost:8000/api/invite?token={user_token} (POST)


## Get pending invites
->Returns the pending invites of the current user.  

    -http://localhost:8000/api/invite/pending?token={user_token} (GET)


## See accepted invite
Returns the meetings that the current user has accepted.  

    -http://localhost:8000/api/invite/accepted?token={user_token} (GET)


## See accepted invite
Returns the meetings that the current user has rejected.  

    -http://localhost:8000/api/invite/accepted?token={user_token} (GET)
   

## Respond to invite
->Returns on success message of the response, and a status code 22.
Required:  
-meeting_id ->id of the meeting  
-response -> response of the current user to the invite {accept, reject}
-id -> ID of the invite  

    -http://localhost:8000/api/invite/response?token={user_token} (POST)


# Variable limits

## Room
1-3

## Month
01-12

## Day
01-31  
-Might need work based on month

## Hour
00-23

## Minute
00-59

## Hour Finish
00-23  
-Must be greater or equal to start  
-Assuming same day


## Install instructions

1. Run: `git clone https://github.com/alinobari/meeting_app.git`;
2. After cloning, run: `composer update`;
3. Then finally: `composer install`;

To setup the database, run `php artisan migrate`. The necessary tables will then be installed.  

## Test the API
A .json file in `tests/AWStest.postman_collection.json` is provided to show the core functionality of the API. Some fields must be filled in by the user, since certain names and token will vary between uses.


# meeting_app
