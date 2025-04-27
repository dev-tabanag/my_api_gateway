Setup Instructions
1. Install XAMPP if you haven't already.
2. Copy the entire my_api_gateway/ folder into your htdocs/ directory (e.g., C:\xampp\htdocs\my_api_gateway).
3. Start Apache and MySQL from the XAMPP Control Panel.
4. Create a MySQL database named gateway using phpMyAdmin.
5. Import your database tables for:
  •api_keys
  •rate_limits
6. Update your database credentials if necessary inside gateway.php:
   $dbUser = "root";
   $dbPass = "root";

Valid API Keys Implemented
  ![image](https://github.com/user-attachments/assets/a7ea16f4-7de9-443d-9067-75c995593403)


How to test features (POSTMAN/ CURL)
  Remember: You must always include the header X-API-Key when making requests.

1. Accessing /users Service.
  •Method: GET
  •URL: http://localhost:8080/my_api_gateway/api/users
  •Headers:
    •X-API-Key: key123
    •X-API-Key: key456
  ![image](https://github.com/user-attachments/assets/0bc78546-8d58-4327-8e5d-208833b2e943)


2.Accessing /products Service.
  •Method: GET
  •URL: http://localhost:8080/my_api_gateway/api/products
  •Headers:
    •X-API-Key: key123
    •X-API-Key: key456
![image](https://github.com/user-attachments/assets/46239aea-8e97-48d2-92d2-44c6ce2bf7d9)


Challenges Faced / Assumptions Made
Challenge: Implementing dynamic rate-limiting for each API key without using external libraries.
Assumption: Every API key is valid indefinitely unless manually removed from the database.
Assumption: Rate limits are set as 10 requests per minute per API key.
Challenge: Handling header retrieval uniformly because different environments (like Apache vs. Nginx) can behave differently with getallheaders().


Unauthorized User:
![image](https://github.com/user-attachments/assets/06a69062-7c43-46d4-8bfa-0d8dd4795ad1)

Rate Limiter:
![image](https://github.com/user-attachments/assets/b70ac9f5-c062-4e40-bf67-30f7930ee769)

Gateway Logs:
![image](https://github.com/user-attachments/assets/be785b81-a539-48b5-b07d-b86b41682c73)










  

