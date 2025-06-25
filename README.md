Composer install

cp .env.example .env

php artisan key:generate

Cấu hính file .env:
APP_URL=http://localhost:8000 //// đường dẫn app để chạy dự án 

DB_CONNECTION=mysql # DB_HOST=127.0.0.1 # DB_PORT=3306 # DB_DATABASE=laravel1 # DB_USERNAME=root # DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=9e00c6100de7b7
MAIL_PASSWORD=7beae4e8d0fb7e
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

QUEUE_CONNECTION=database (Để sử dụng gửi mail thông qua queue và job)
QUEUE_CONNECTION=sync (Để gửi mail trực tiếp với tiến trình khi gọi tới job)

php artisan migrate

php artisan db:seed

php artisan queue:work // để chạy queue tự động gửi mail php artisan queue:work --daemon

// Nếu chạy dự án mà bị lỗi không gửi được mail thì tạo inbox mới trong mailtrap và cấu hình lại

npm install
npm run dev
php artisan serve