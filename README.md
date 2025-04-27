"# showorm" 
require 'src/ShowORM.php';
class_alias('ShowORM', 'DB');

connect :
DB::connect('localhost', 'db name', 'user name', 'password ' , 'db port' , 'utf8mb4');

create table:
DB::create_table('table name', [
    'id' => 'INT PRIMARY KEY AUTO_INCREMENT',
    'name' => 'VARCHAR(255) NOT NULL',
    'email' => 'VARCHAR(255) UNIQUE',
    'age' => 'INT',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
]);

get:
$users = DB::table('users')->get('fetchAll');

$users = DB::table('users')->find(id);

$users = DB::table('users')->where('id', '>', 2)->get('fetchAll');

count:
$count = DB::table('users')->count('*' , 'total')->get();
echo $count[0]['total'];

insert:
DB::table('users')->insert([
    'col name' => 'value',
    'col name' => 'value'
]);

update:
DB::table('users')->update(id, [
    'col name' => 'value'
]);

delete:
DB::table('users')->delete(id);

