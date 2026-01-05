composer install
php setup_db.php
php -S localhost:8080 -t public



PHP MVC Interview Coding Guide
Use this guide to structure your thought process during a live coding interview. It breaks down each file into logical "sections" that you can code one by one.

1. src/Core/Database.php
One-Liner: "I'll start with a Singleton Database class to handle our PDO connection efficiently."

Section 1: Singleton Structure
Goal: Ensure only one DB connection exists.
Code:
  private static $instance = null;
  private $connection;
  getInstance(): Returns self::$instance (lazy loading).
  
Section 2: Constructor (The Connection)
Goal: Connect to MySQL.
Code:
  Load env vars ($_ENV['DB_HOST'] etc.).
  new PDO($dsn, $user, $pass, $options).
  Crucial: Set PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION to catch SQL errors.
  
Section 3: Accessor
Goal: Let other classes get the PDO object.
Code: 
  getConnection()
   returns $this->connection.

   
2. src/Core/Router.php
One-Liner: "Next, I need a Router to map URLs to my Controllers."

Section 1: Properties & Grouping
Goal: Store routes and handle prefixes (like /auth).
Code:
  $routes = [] (Nested array: method -> path -> callback).
  $groupPrefix = ''.
  group($prefix, $callback)
  : Append prefix, run callback, restore prefix.

Section 2: Registration Methods
Goal: Add routes to the array.
Code:
  get($path, $callback) / post(...).
  Prepend $this->groupPrefix to $path.
  Store in $this->routes['GET'][$path].

Section 3: Dispatcher (The Engine)
Goal: Find the matching route and run it.
Code:
  Get URI: parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH).
  Get Method: $_SERVER['REQUEST_METHOD'].
  Check: isset($this->routes[$method][$path]).
  Run: If array [Class, Method], (new Class)->method().

  
3. src/Core/Controller.php
 (Base Controller)
One-Liner: "I'll create a Base Controller to handle common API tasks like JSON responses."

Section 1: JSON Helper
Goal: Standardize responses.
Code: 
  jsonResponse($data, $status) -> header(...), http_response_code(...), echo json_encode(...).

Section 2: Input Helper
Goal: Read JSON body (since $_POST doesn't work for JSON).
Code: 
  getInput() -> json_decode(file_get_contents('php://input'), true).


4. src/Models/User.php
One-Liner: "Now the User model to interact with the database."

Section 1: Setup
Goal: Get DB connection.
Code: Constructor calls Database::getInstance()->getConnection().

Section 2: Create User
Goal: Register a new user.
Code:
  password_hash($pass, PASSWORD_BCRYPT).
  prepare("INSERT INTO ...") & execute().
  
Section 3: Find User
Goal: Login check.
Code: prepare("SELECT * FROM users WHERE email = ?") & fetch().


5. src/Controllers/AuthController.php
One-Liner: "This is the core logic for Registration and Login."

Section 1: Register
Goal: Create a user if they don't exist.
Code:
  Validate input (name, email, pass).
  Check findByEmail (409 if exists).
  Call create (201 if success).

Section 2: Login
Goal: Verify credentials and issue JWT.
Code:
  Find user.password_verify($inputPass, $dbPass).
  JWT: Create payload (iss, exp, sub). Encode with JWT::encode.
  Return token.


6. src/Middleware/AuthMiddleware.php
One-Liner: "I need middleware to protect my private routes."

Section 1: Header Extraction
Goal: Get the Bearer token.
Code:
  Try apache_request_headers() or $_SERVER['HTTP_AUTHORIZATION'].
  Regex: preg_match('/Bearer\s(\S+)/', ...).

Section 2: Validation
Goal: Verify the token is real.
Code:
  JWT::decode($token, new Key($secret, 'HS256')).
  Wrap in try/catch (return 401 if invalid).


7. public/index.php
One-Liner: "Finally, I'll wire everything together in the entry point."

Section 1: Initialization
Goal: Load deps and env.
Code: require vendor/autoload, Dotenv::createImmutable, new Router.

Section 2: Route Definitions
Goal: Define the API surface.
Code:
  $router->group('/auth', ...) for login/register.
  $router->post('/query', ...) for raw SQL.

Section 3: Dispatch
Goal: Run the app.
Code: $router->dispatch().
