# Docker

For a quick-start we now provide a simple docker-compose.yml file. Be aware that this is just for demo purposes. Please do not use this in production as it lacks ssl support. We have plans to add support for let's encrypt, but did not find time for that so far.

To get started

1.) Install docker and docker compose
2.) Download the Docker folder
3.) Run docker-compose up -d  
    On port 80 you will find the speechday login (admin/admin)
    On port 8080 you will find phpmyadmin, which of course has a link to the speechday database
