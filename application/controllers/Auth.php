<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Auth extends CI_Controller
{
	/**
	 * Yang bisa mengakses controller auth hanyalah user yang belum login
	 * Jika user sudah login maka secara otomatis akan diredirect ke
	 * profile halamannya sendiri.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('customer_model');
		$this->load->helper('form');

		// Kalau sudah login di redirect ke Profile Controller
		if ($this->session->userdata('p_username')) {
			redirect('profile');
		}
	}

	/**
	 * Langsung Cek Login
	 */
	public function index()
	{
		redirect('auth/login');
	}

	/**
	 * Show the Register form and Check User Registration Data
	 * before inserting to the database
	 */
	public function register()
	{
		$this->load->helper('form');
		if ($this->input->post('btnRegister')) {
			$this->load->library('form_validation');
			$this->load->database();
			$this->form_validation->set_rules('username','Username','max_length[15]|required|trim|min_length[4]|alpha_numeric|is_unique[users.username]');
			$this->form_validation->set_rules('password','Password','max_length[15]|required|trim|min_length[6]|alpha_numeric');
			$this->form_validation->set_rules('email','Email','required|trim|valid_email|is_unique[users.email]');
			$this->form_validation->set_rules('conf_password','Confirm Password','required|matches[password]|trim');
			$this->form_validation->set_rules('agreeTerms','Terms and Conditions','required');
			if ($this->form_validation->run()) {
				$this->load->library('email');
				$this->email->to($this->input->post('email'));
				$this->email->from('admin@shirokumaonline.co.vu', 'Shirokumaonline');
				$this->email->subject('Welcome to Shirokumaonline');
				$this->email->message($this->load->view('email/welcome', null, true));
				if ($this->email->send()) {
					$success = $this->customer_model->registerUser($this->input->post('username'), $this->input->post('email'), $this->input->post('password'));
					$this->session->set_flashdata('alert_level', 'success');
					$this->session->set_flashdata('alert', '<strong>Menunggu Verifikasi Email Anda!</strong><br/>Terima kasih telah bergabung dengan Shirokumaonline');
					$this->email->to($this->input->post('email'));
					$this->email->from('admin@shirokumaonline.co.vu', 'Shirokumaonline');
					$data['link'] = anchor('auth/activate?key='.$success, 'Activate Your Account Now');
					$this->email->subject('Confirm your account | Shirokumaonline');
					$this->email->message($this->load->view('email/confirm', $data, true));
					if (!$this->email->send()) {
						$this->session->set_flashdata('alert_level', 'danger');
						$this->session->set_flashdata('alert', $this->email->print_debugger());
					}
					$data['title'] = "Register to Shirokumaonline";
					$this->load->view('header',$data);
					$this->load->view('user/register');
					$this->load->view('footer');
				} else {
					$this->session->set_flashdata('alert_level', 'danger');
					$this->session->set_flashdata('alert', 'Email tidak valid.' . $this->email->print_debugger());
					//echo $this->email->print_debugger();
					$data['title'] = "Register to Shirokumaonline";
					$this->load->view('header',$data);
					$this->load->view('user/register');
					$this->load->view('footer');
				}
			}
		}
		else {
			$data['title'] = "Register to Shirokumaonline";
			$this->load->view('header',$data);
			$this->load->view('user/register');
			$this->load->view('footer');
		}
	}

	/**
	 *  Show the Login Form
	 */
	public function login()
	{
		$data['title'] = "Login to Shirokumaonline";
		$this->load->helper('form');
		$this->load->view('header',$data);
		$this->load->view('user/login');
		$this->load->view('footer');
	}

	/**
	 * Method yang digunakan untuk Sign In secara umum pada customer
	 * Disini terdapat pengecekan untuk Customer. Customer yang dapat login hanyalah
	 * Customer yang sudah melakukan verifikasi email dan Customer dengan user_role =3 atau customer_verified
	 */
	public function do_login()
	{
		if ($this->input->post('btnSignIn')) {
			$cekLogin = $this->customer_model->checkUserLogin($this->input->post('username'), $this->input->post('password'));
			if ($cekLogin == 0) {
				$this->session->set_flashdata('alert_level', 'danger');
				$this->session->set_flashdata('alert', 'Username / Password doesn\'t match.');
				redirect('auth/login');
			} else {
				if ($cekLogin == -1) { // Jika dia adalah user yang belum melakukan verifikasi email.
					$this->session->set_flashdata('alert_level', 'danger');
					$this->session->set_flashdata('alert', 'Please verify your email before login.');
					redirect('auth/login');
				} else {
					if (strpos($this->input->post('username'),'@')) {
						$cekLogin = $this->customer_model->getUsernameByEmail($this->input->post('username'));
					} else {
						$cekLogin = $this->input->post('username');
					}
					$this->session->set_userdata('p_username', $cekLogin);
					if ($this->session->userdata('current_url')) {
						$temp = $this->session->userdata('current_url');
						$this->session->unset_userdata('current_url');
						redirect($temp);
					} else {
						if ($this->input->post('current_url')) {
							redirect($this->input->post('current_url'));
						}
					}
					redirect('profile');
				}
			}
		} else {
			redirect('auth');
		}
	}

	public function activate()
	{
		if ($this->input->get('key')) {
			$success = $this->customer_model->activateCustomer($this->input->get('key'));
			if ($success) {
				$this->session->set_flashdata('alert_level', 'success');
				$this->session->set_flashdata('alert', 'Successfully activate the account. Now, you can login.');
			} else {
				$this->session->set_flashdata('alert_level', 'danger');
				$this->session->set_flashdata('alert', 'Cannot activate user by given credentials.');
			}
			redirect('auth/login');
		}
		//redirect('auth');
	}

	public function forgot_password()
	{
		$data['title'] = "Reset Password | Aftervow";
		if ($this->input->post('btnResetPassword')) {
			$this->load->library('form_validation');
			$this->form_validation->set_rules('reset_email','Reset Email','required|trim|valid_email');
			if ($this->form_validation->run()) {
				$email = $this->input->post('reset_email');
				$key = $this->customer_model->resetCustomerPassword($email);
				if ($key == -1) { // Email belum terdaftar
					$this->session->set_flashdata('alert_level', 'warning');
					$this->session->set_flashdata('alert', 'There\'s no user registered with that email.');
					redirect('auth/forgot_password');
				} else {
					// Send Email && Reset the PASSWORD

					$this->load->library('email');
					$this->email->to($email);
					$this->email->from('admin@shirokumaonline.co.vu','Admin Shirokumaonline');
					$this->email->subject('Reset your account');
					$data['link'] = anchor('auth/reset_password?forgot_key='.$key, 'Reset Password');
					$this->email->message($this->load->view('email/reset_password',$data,true));
					if ($this->email->send()) {
						$this->session->set_flashdata('alert_level', 'success');
						$this->session->set_flashdata('alert', 'Email Sent. Please Check your email to reset your password.');
						redirect('auth/forgot_password');
					} else {
						$this->session->set_flashdata('alert_level', 'danger');
						$this->session->set_flashdata('alert', 'Cannot Send Email');
						redirect('auth/forgot_password');
					}
				}
			}
		}
		$this->load->view('header',$data);
		$this->load->view('user/forgot_password');
		$this->load->view('footer');
	}

	public function reset_password()
	{
		$key = '';
		if ($this->input->post('btnChangeReset')) { // Password sudah disubmit
			$this->load->library('form_validation');
			$this->form_validation->set_rules('password','Password','max_length[15]|required|trim|min_length[6]|alpha_numeric');
			$this->form_validation->set_rules('conf_password','Confirm Password','required|matches[password]|trim');
			$key = $this->encrypt->decode($this->input->post('key'));
			if ($this->form_validation->run()) {
				$success = $this->customer_model->resetPassword($key,$this->input->post('password'));
				if ($success) {
					$this->session->set_flashdata('alert_level', 'success');
					$this->session->set_flashdata('alert', 'Successfully reset your password. Now you can login as usual.');
					redirect('auth/login');
				}
			}
		}
		if ($this->input->get('forgot_key') || $key != '') {
			if ($key == '') { // Jika kosong berarti mengambil data dari get
				$key = $this->input->get('forgot_key');
			}
			if ($this->customer_model->isForgotKeyExist($key)) {
				$data['title'] = 'Confirm Your New Password | Aftervow';
				$data['key'] = $this->encrypt->encode($key);
				$this->load->view('header',$data);
				$this->load->view('user/change_password_after_reset',$data);
				$this->load->view('footer');
			} else {
				redirect('/');
			}
		} else {
			redirect('auth');
		}
	}
}
