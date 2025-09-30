<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Tampilkan semua data product Beserta pemiliknya/user.
     */
    public function index()
    {

        // untuk memanggil relasi terkait, sebutkan
        // nama method relasi yang ada di model tersebut
        // Gunakan method with() untuk menyertakan relasi tabel
        // pada data yang di panggil
        $products = Product::query()
        ->where('is_available', true)  // hanya product tersedia
        ->with('user')                  // sertakan pemiliknya
        ->get();                        // eksekusi query
        // format respon data 
        return response()->json([
            'status' => 'Sukses',
            'data'=>$products
        ]);
    }

    /**
     * cari produk berdasarkan 'name'
     * dan ikutkan relasinya
     */
    public function search(Request $req)
    {
        try {
            //validasi minimal 3 huruf untuk pencarian
            $validated = $req->validate([
                'teks' => 'required|min:3',
            ], [
                //pesan error custom
                'teks.required' => ':attribute jangan dikosongkan lah!',
                'teks.min'      => 'Ini :attribute kurang bos!',
            ], [
                //custom attributes
                'teks' => 'huruf'
            ]);

            // proses pencarian produk berdasarkan teks yang dikirim
            $products = Product::query()
            ->where('name', 'like', '%'.$req->tesk.'%')
            ->with('user')
            ->get();
            //return hasil pencarian
            return response()->json([
                'pesan' => 'Sukses!',
                'data'  => $products,
            ]);
        } 
        //use Illuminate\Validation\ValidationException; 
        catch (ValidationException $ex) {
            return response()->json([
                'pesan' => 'Gagal!',
                'data'  => $ex->getMessage(),
            ]);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Create
     * simpan produk baru ke database
     */
    public function store(Request $request)
    {
        try {
            //
            $validated = $request->validate([
                'user_id'       => 'nullable|exists:users,id',
                'name'          => 'required|string|max:255',
                'price'         => 'required|numeric|min:0',
                'stock'         => 'required|integer|min:1',
                'description'   => 'nullable|string',
                'is_available'  => 'boolean',
            ], [
                'name.required'      => 'Nama produk jangan dikosongkan',
                'price.required'     => 'Harga wajib diisi',
                'price.numeric'      => 'Harga harus berupa angka',
                'stock.required'     => 'Stok wajib diisi',
                'stock.integer'      => 'Stok harus berupa angka'
            
            ]);

            $product = Product::create($validated);

            // return hasil simpan
            return response()->json([
                'pesan'     => 'Produk Berhasil ditambahkan',
                'data'      => $product,
            ]);
        } catch (ValidationException $ex) {
            return response()->json([
                'pesan'     =>'Gagal',
                'data'      => $ex->getMessage(),
            ]);
        }

        //
    }

    /**
     * Read
     */
    public function show($id)
    {
        //mencari produk berdasarkan id
        $product = Product::with('user')->find($id);

        // produk tidak ditemukan
        if (!$product) {
            return response()->json(['pesan' => 'Produk tidak ditemukan']);
        }
        // jika produk ada tampilkan 
        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     *update
     */
    public function update(Request $r, $id)
    {
        try {
            // cari produk
            $product = Product::findOrFail($id);

            // validasi ubah data
            $validate = $r->validate([
                'name'      => 'string|max:255',
                'price'     => 'numeric|min:0',
                'image_path' => 'nullable|string',
                'stock'     => 'integer|min:1',
                'description' => 'nullable|string',
                'is_available' => 'boolean'
            ]);
            // ---- cara sederhana
        $product->update([
            'name'        => $r->name ?? $product->name,
            'price'       => $r->price ?? $product->price,
            'stock'       => $r->stock ?? $product->stock,
            'description' => $r->description ?? $product->description,
            'is_available'=> $r->is_available ?? $product->is_available,
        ]);

        // ---- cara yang lebih "complete"
        $data = $r->all();

        $product->update([
            'name'        => $data['name'] ?? $product->name,
            'price'       => $data['price'] ?? $product->price,
            'stock'       => $data['stock'] ?? $product->stock,
            'description' => $data['description'] ?? $product->description,
            'is_available'=> $data['is_available'] ?? $product->is_available,
        ]);

        // kembalikan data produk yang sudah diubah beserta pesan sukses
        return response()->json([
            'pesan'  => 'Produk berhasil diubah!',
            'produk' => Product::with('user')->find($product->id)
        ]);

    } catch (ValidationException $e) {
        return response()->json([
            'pesan' => 'Validasi gagal!',
            'error' => $e->validator->errors(),
        ]);
    }
}


    /**
     * D = Delete
     * Hapus produk berdasarkan id
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'pesan' => 'Produk tidak ditemukan'
            ]);
        }

        $product->delete();

        return response()->json([
            'pesan' => 'Produk berhasil dihapus!'
        ]);
    }
}