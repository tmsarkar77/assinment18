<!-- Task 1:
Create a new migration file to add a new table named "categories" to the database. The table should have the following columns: -->
php artisan make:migration create_categories_table --create=categories

Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });


<!-- Task 2
Create a new model named "Category" associated with the "categories" table. Define the necessary properties and relationships. -->

php artisan make:model Category 

class Category extends Model
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

<!-- Task 3:
Write a migration file to add a foreign key constraint to the "posts" table. The foreign key should reference the "categories" table on the "category_id" column. -->

php artisan make:migration add_category_id_to_posts_table --table=posts

Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable();

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('SET NULL');
        });


<!-- Task 4:
Create a relationship between the "Post" and "Category" models. A post belongs to a category, and a category can have multiple posts. -->

class Post extends Model
{
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

<!-- In the "Category" model: -->

class Category extends Model
{

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

<!-- Task 5:
Write a query using Eloquent ORM to retrieve all posts along with their associated categories. Make sure to eager load the categories to optimize the query. -->

$posts = Post::with('category')->get();

foreach ($posts as $post) {
    echo "Post: {$post->title} <br>";
    echo "Category: {$post->category->name} <br>";
    
}

<!-- Task 6:
Implement a method in the "Post" model to get the total number of posts belonging to a specific category. The method should accept the category ID as a parameter and return the count. -->

class Post extends Model
{

    public function getPostsCountByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)->count();
    }
}

$categoryPostsCount = $post->getPostsCountByCategory($categoryId);


<!-- Task 7:
Create a new route in the web.php file to handle the following URL pattern: "/posts/{id}/delete". Implement the corresponding controller method to delete a post by its ID. Soft delete should be used. -->
Route::delete('/posts/{id}/delete', [PostController::class, 'delete'])->name('posts.delete');

public function delete($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }

<!-- Task 8:
Implement a method in the "Post" model to get all posts that have been soft deleted. The method should return a collection of soft deleted posts. -->

class Post extends Model
{
    use SoftDeletes;

    public static function getSoftDeletedPosts()
    {
        return static::withTrashed()->whereNotNull('deleted_at')->get();
    }
}

$softDeletedPosts = Post::getSoftDeletedPosts();

<!-- Task 9:
Write a Blade template to display all posts and their associated categories. Use a loop to iterate over the posts and display their details. -->

<!-- posts.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>All Posts</h1>

        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($posts as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td>{{ $post->category->name }}</td>
                        <td>{{ $post->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection


<!-- Task 10:
Create a new route in the web.php file to handle the following URL pattern: "/categories/{id}/posts". Implement the corresponding controller method to retrieve all posts belonging to a specific category. The category ID should be passed as a parameter to the method. -->

Route::get('/categories/{id}/posts', [CategoryController::class, 'getPostsByCategory'])->name('categories.posts');

class CategoryController extends Controller
{
    public function getPostsByCategory($id)
    {
        $category = Category::findOrFail($id);
        $posts = $category->posts;

        return view('category_posts', compact('category', 'posts'));
    }
}

<!-- Task 11:
Implement a method in the "Category" model to get the latest post associated with the category. The method should return the post object. -->

public function latestPost()
    {
        return $this->hasOne(Post::class)->latest();
    }

$category = Category::find($categoryId);
$latestPost = $category->latestPost;

<!-- Task 12:
Write a Blade template to display the latest post for each category. Use a loop to iterate over the categories and display the post details. -->

<!-- latest_posts.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Latest Posts for Each Category</h1>

        <table class="table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Latest Post</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->latestPost->title }}</td>
                        <td>{{ $category->latestPost->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection


