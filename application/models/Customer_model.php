<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Customer_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library('encrypt');
	}
	public function registerUser($username, $email,  $password)
	{
		$rand = getToken(32);
		$data = [ 'username' => $username ,
				'password' => $this->encrypt->encode($password),
				'email' => $email,
                'kode_verifikasi' => $rand,
                'role' => 'customer',
				'status' => 0 // tidak konfirmasi tidak dianggap sebagai user
				];
		$this->db->set('tanggal_lahir','now()',false);
		$this->db->insert('users', $data);
		// Mengirimkan Verifikasi Email
		if ($this->db->affected_rows()) {
			return $rand;
		}
		return 0;
	}
	public function checkUserLogin($username, $password)
	{
		$this->db->select('password, status,  role');
		$this->db->where('username',$username);
		$this->db->or_where('email',$username);
		$result = $this->db->get('users')->row();
		if ($this->db->affected_rows() > 0) {
			if ($result->role == 'customer') { //Jika User adalah customer
				// $correctPassword = $this->encrypt->decode($result->password);
				$correctPassword = $result->password;
				if ($correctPassword == $password) {
					if ($result->status == 0) { // Jika belum terverifikasi
						return -1;
					}
					return 1;
				}
			}
		}
		return 0;
	}
	public function getUser($username)
	{
		$this->db->where('username', $username);
		$this->db->where('status >',0);
		return $this->db->get('users')->row();
	}
	public function getUsernameByEmail($email)
	{
		$this->db->select('username');
		$this->db->where('email',$email);
		return $this->db->get('users')->row()->username;
	}
	public function updateUserProfile($username,$nama_depan, $nama_belakang, $tgl_lahir,$alamat, $kota,$provinsi, $telepon, $provinsi_id, $kota_id)
	{
		$data = ['nama_depan' => $nama_depan,
            'nama_belakang' => $nama_belakang,
            'alamat' => $alamat,
            'kota' => $kota,
            'telepon' => $telepon,
            'provinsi' => $provinsi,
            'provinsi_id' => $provinsi_id,
            'kota_id' => $kota_id
        ];
		$this->db->set('tanggal_lahir', date_format(date_create_from_format('d/m/Y',$tgl_lahir),'Y-m-d'));
		$this->db->where('username',$username);
		$this->db->update('users',$data);
		return $this->db->affected_rows();
	}
	public function setPassword($username,$password)
	{
		$this->db->where('username',$username);
		$this->db->set('password',$this->encrypt->encode($password));
		$this->db->update('users');
		return $this->db->affected_rows();
	}

	public function activateCustomer($key)
	{
		// Activate User
		$this->db->set('status',1);
		$this->db->where('kode_verifikasi',$key);
		$this->db->update('users');

		// Hapuskan Users_verification_code
		$this->db->set('kode_verifikasi','');
		$this->db->where('kode_verifikasi',$key);
		$this->db->update('users');

		return $this->db->affected_rows();
	}
	public function resetCustomerPassword($email)
	{
		$this->db->where('email', $email);
		$this->db->get('users');
		if ($this->db->affected_rows() == 0) {
			return -1; // There is no account with that emails
		}
		$key =  getToken(32);
		$data = ['kode_verifikasi' => $key];
		$this->db->where('email', $email);
		$this->db->update('users',$data);
		return $key;
	}
	public function isForgotKeyExist($key)
	{
		$this->db->where('kode_verifikasi',$key);
		$this->db->from('users');
		return $this->db->count_all_results();
	}
	public function resetPassword($key, $password)
	{
		$this->db->select('username');
		$this->db->where('kode_verifikasi',$key);
		$this->db->from('users');
		$username = $this->db->get()->row()->username;
		// Hapuskan Users_verification_code
		$this->db->set('kode_verifikasi','');
		$this->db->where('username',$username);
		$this->db->update('users');
		return $this->setPassword($username,$password);
	}
	public function getListProvince()
	{
		$this->load->library('curl');
		$arr = json_decode($this->curl->simple_get('http://api.rajaongkir.com/starter/province',['key' => '967981f611f67c550e56affde8f2ac29']));
		$arrProvince = $arr->rajaongkir->results;
		$provinces = [];
		foreach ($arrProvince as $province) {
			$provinces[$province->province_id] = $province->province;
		}
		return $provinces;
	}
	public function getListCity($province_id)
	{
		$this->load->library('curl');
		$arr = json_decode($this->curl->simple_get('http://api.rajaongkir.com/starter/city',
            ['key' => '967981f611f67c550e56affde8f2ac29',
                'province' => $province_id
            ]
        ));
		$arrCity = $arr->rajaongkir->results;
		$cities = [];
		foreach ($arrCity as $city) {
			$cities[$city->city_id] = $city->city_name;
		}
		return $cities;
	}
}
