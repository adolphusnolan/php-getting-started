
# PHP HTML Parser Solution  

## Overview  
This project provides a PHP-based solution to parse HTML files uploaded by customers. Each HTML file adheres to a predefined format, although the number of records may vary. The parsed data is stored in a PostgreSQL database, with records tagged by report date and report number.  

## Prerequisites  
- **Apache Server:** Ensure you have an Apache server installed and running to host the project.  
- **PostgreSQL:** Install PostgreSQL to serve as the database for storing parsed data.  

## Getting Started  

### 1. Set Up the Database Connection  
To configure the database connection:  

- Open the `actions/db.php` file.  
- Update the following settings with your PostgreSQL credentials:  
  ```php
  // Database connection setup
  $host = "localhost";      // Your database host
  $port = "5432";           // Default PostgreSQL port
  $dbname = "database_Name"; // Replace with your database name
  $user = "postgres";        // Replace with your PostgreSQL username
  $password = "root";        // Replace with your PostgreSQL password

### 2. Import the Database Table
To create the required table in your PostgreSQL database:

Open a PostgreSQL client (e.g., pgAdmin or the command line).
Run the following SQL query to create the tbl_rating table:
  CREATE TABLE tbl_account_history_v3 (
      account_history_id SERIAL PRIMARY KEY,
      reference VARCHAR(50),
      report_date VARCHAR(50),
      furnisher VARCHAR(255),
      account VARCHAR(255),
      category VARCHAR(255),
      chester_pa BYTEA,
      allen_tx BYTEA,
      atlanta_ga BYTEA,
      account_history_created_date DATE
  );



### 3. Deploy the Project
To deploy the project:

Place the project files in the root directory of your Apache server (e.g., /var/www/html).
Ensure your server is properly configured to handle PHP scripts.

### 4. Upload HTML Files
Once deployed, you can use the provided interface to upload HTML files. The files will be parsed, and the extracted data will be saved into the PostgreSQL database.



