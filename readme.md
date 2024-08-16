
Open your php.ini file (you can find it in your PHP installation directory).
Uncomment the following lines by removing the semicolon (;) at the beginning:
ini
Copy code
extension=gd
extension=zip
Save the file and restart your web server

cd C:\xampp\htdocs\deploymentTaskScheduler
composer install
composer require phpoffice/phpspreadsheet