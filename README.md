# SpeechDay

This project can be used to easily and effectively hold a speechday (a parent-teacher-meeting) in your school.
The administrator can import data for teachers and students and create a speechday-event.
The newsletter filled with the needed access data so that parents can log in and book their desired time slots for the teachers they want to meet can then be created automatically. 

## Installation

You can create the needed database with the SQL script provided in the setup folder.
Furthermore you have to enter your database credentials in the settings.ini file contained in code/dao.
If you want to quickly try out the tool there is also a SQL script provided in the setup folder which puts some dummy test data in your newly created database. 

Be aware that the UI is in german.

## Usage

As an administrator:

1. Import teachers via a CSV file.
2. Import student data via a CSV file.
3. Upload a newsletter template in ODT format.
4. Create a speechday-event.
5. Create the newsletter and distribute it among the students / parents.

As a teacher (optional):

1. Set the time range you are present.

As a student / parent:

1. Log in with the credentials provided on the newsletter.
2. Book the desired slots for the desired teacher.
3. Print your time-table.

## Online Speech day
We added support to hold the speech day online. When you create a new speech day event you can now specify a base URL for a video conferencing service. (E.g. https://meet.jit.si)

Teachers and students will find an individal link for each booked slot, where they can meet online.
