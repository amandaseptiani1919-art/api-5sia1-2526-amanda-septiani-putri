<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

//default endpoint API: http://api-amanda.test/api

// Product Controler
//menampung semua logika dan perinta
//dari endpoint url disini (api.php)
use App\Http\Controllers\ProductController;

/**
 * Api Resource untuk model Product
 */
// 1. ambil semua data Produk beserta pemiliknya (user)
// action url = [NamaControler::class, 'method']
Route::get('/products/semuanya', [ProductController::class, 'index']);

// 2. cari produk tersedia berdasarkan nama
Route::get('/products/cari', [ProductController::class, 'search']);

// 3. tambah produk baru
Route::post('/products/tambah', [ProductController::class, 'store']);

// 4. read detal produk berdasarkan id
Route::get('/products/{id}', [ProductController::class, 'show']);

// 5. update produk 
Route::put('/products/update/{id}', [ProductController::class, 'update']);

// 6. hapus produk
Route::delete('/products/delete/{id}', [ProductController::class, 'destroy']);


// route ambil semua data user
// Method Get
Route::get('/users',function(){
    // Panggil semua data user dan simpan dalam variabel $User
    // method with digunakan untuk mengikutsertakan relasi
    // relasi yang disebutkan sesuai dengan nama method pada model
    $users = User::query()->with('products')->get();
    // Kembalikan data user dalam bentuk JSON
    $json_users = json_encode($users);
    // berikan data (response) json ke apliasi yang meminta(request)
    return $json_users;
});

// route cari user berdasarkan id
// Method Get
Route::get('/user/find', function(Request $request){
    // cari user
    // $user = User::find($request->id);
    // cari user dengan relasi
    $user = User::query()
                ->where('id', $request->id)
                ->with('products')
                ->get();
// dd ($user); // dump and die
    return json_encode($user);
});

// route cari user berdasarkan kemiripan nama atau email
// Method Get
Route::get('/user/search', function(Request $request){
    // cari user berdasarkan string nama
    $users = User::where('name', 'like', '%'.$request->nama.'%')
    ->orWhere('email', 'like', '%'.$request->nama.'%')->get();
    // SELECT * FROM users WHERE name OR email LIKE '%ahmad%';
    return json_encode($users);
});

// registrasi user
// parameter nama, email, phone, password
// password harus di hash sebelum disimpan ke table


Route::post('/register', function (Request $r) {
   try {
     // validasi data
    $validate = $r->validate([
        // params => rules
        'nama' => 'required|max:255',
        'surel' => 'required|email|unique:users,email',
        'telp' => 'required|unique:users,phone',
        'sandi' => 'required|min:6'
    ]);
        // tambahkan data userbaru
        $new_user = User::query()->create([
            // field => params
            'name' => $r->nama,
            'email' => $r->surel,
            'phone' => $r->telp,
            'password' => Hash::make($r->sandi)
        ]);
        return response()->json($new_user);
   } catch (ValidationException $e){
    return $e->validator->errors();
   }

});

// Ubah Data User
// parameter nama, surel, telp, sandi
// method 'PUT' atau 'PATCH'
// data user yang akan diubah dicari berdasarkan id yang dikirim
// pada contoh ini, id akan lansung diasosiasikan ke model user
Route::put('/user/edit/{user}', function (Request $r, User $user) {
    

    try {
        // validasi ubah data
     $validate = $r->validate([
        'nama' => 'max:255',
        'surel' => 'email|unique:users,email,'.$user->id,
        'telp' => 'required|unique:users,phone,'.$user->id,
        'sandi' => 'min:6'
    ]);

    // ---- cara yang sederhana
    $user->update([
        'name' => $r->nama ?? $user->name,
        'email' => $r->surel ?? $user->email,
        'phone' => $r->telp ?? $user->phone,
        'passowrd' => $r->sandi
                        ? Hash:: make($r->sandi)
                        : $user->password
    ]);

    // ----- cara yang complate
    // salin data yang diterima ke variabel baru
    $data = $r->all();
    // jika ada password pada array $data
    if ($r->array_key_exists('sandi', $data)){
        // replace isi 'sandi' dengan hasil Hash 'sandi'
        $data['sandi'] = Hash::make($data['sandi']);

    }

    // ubah data user
    $user->update([
        'name' => $data['nama'] ?? $user->name,
        'email' => $data['surel'] ?? $user->email,
        'phone' => $data['telp'] ?? $user->phone,
        'password' => $data['sandi'] ?? $user->password

    ]);
    // berakhirnya cara yang compleks


    // kembalikan data user yang sudah diubah beserta pesan sukses
    return response()->json([
        'pesan' => 'Sukses diubah!', 'user' => $user,
    ]);

        // code
        } catch (ValidationException $e){
    return $e->validator->errors();
   }

});

//hapus data user
//method 'DELETE'
//request dilakukan dengan menyertakan  id user yang akan dihapus
Route::delete('/user/delete', function (Request $r)
{
    //temukan user berdasarkan id yang dikirim
    $user = User::find($r->id);
    // respon jika user tidak ditemukan
    if (! $user)
        return response() -> json([
    'pesan' => 'Gagal! User Tidak Ditemukan'
        ]);

    //hapus data user jika ada
    $user->delete();
    return response()->json([
        'pesan' => 'Sukses! User berhasil dihapus'
    ]);
});


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');