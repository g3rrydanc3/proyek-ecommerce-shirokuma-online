<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Model_wish_list extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->load->database();
	}
	function insert($data){
		$this->db->insert('wish',$data);
		return $this->db->affected_rows() > 0;
	}
	function remove($username, $id_barang){
		if($this->is_exist($username, $id_barang))
		{
			$this->db->delete("wish", ["username" => $username, "id_barang" => $id_barang], 1);
		}
	}
	function get_wish($username){
		return $this->db->get_where("wish",['username' => $username])->result();
	}
	function is_exist($username, $id_barang){
		$this->db->where(['username' => $username, 'id_barang' => $id_barang])->limit(1);
		return $this->db->count_all_results("wish") > 0;
	}
	function detail($id)
	{
		return $this->db->get_where('barang', ['id'=>$id], 1)->row();
	}
}