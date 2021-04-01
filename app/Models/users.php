<?php

    namespace App\Models;
    use CodeIgniter\Database\ConnectionInterface;

	class users
	{
		function __construct(ConnectionInterface &$db)
        {
            $this->db = &$db;
            // $db = \Config\Database::connect();
        }
		
		private function sendMail($type, $hash, $id, $email)
		{
			switch($type)
			{
				case 1:
					$body = ("Hello").",\n\n"._("You, or someone that knows your email address,")."\n"._("just signed up with paste.pryv8.org")."\n\n";
					$body .= _("Please click on the following URL to confirm your email address:")."\n\n";
					$body .= "https:/"."/paste.pryv8.org/verify?hash=$hash&uid=$id\n\n";
					mail($email, "[Pryv8bin]: "._("Email Verification Check"), $body, "From: noreply@pryv8.org\nReturn-Path: noreply@pryv8.org","-f noreply@pryv8.org");
					break;

				case 2:
					$body = ("Hello").",\n\n"._("You, or someone that knows your email address,")."\n"._("requested to reset your password on paste.pryv8.org")."\n\n";
					$body .= _("Please click on the following URL to reset your password:")."\n\n";
					$body .= "https:/"."/paste.pryv8.org/reset?hash=$hash&uid=$id\n\n";
					mail($email, "[Pryv8bin]: "._("Password Reset"), $body, "From: noreply@pryv8.org\nReturn-Path: noreply@pryv8.org","-f noreply@pryv8.org");
					break;
				
				default:
				exit;
			}
		}

		public function verify($hash, $uid)
		{
			$query = $this->db->query("SELECT 1 FROM `auth` WHERE `hash`='$hash' AND `uid`= $uid");
			if($query->getNumRows() <= 0)
				return false;
			$query = $this->db->query("UPDATE `auth` SET `hash`=NULL WHERE `uid`=$uid");
			return true;
		}

		private function genHash()
		{
			$rnd = fopen("/dev/urandom", "r");
			$hash = md5(fgets($rnd, 64));
			return $hash;
		}

		public function registerUser($email, $password)
		{
			$hash = $this->genHash();
			$username = strtok($email, '@'); 
			$data = ["username" => $username,
						"email" => $email,
						"password" => $password,
						"hash"=> $hash
					];
			$this->db->table('auth')
			->insert($data);
			$id = $this->db->insertID();
			$this->sendMail(1, $hash, $id, $email);
		}

		public function tryLogin($email, $password)
		{
			$query = $this->db->query("SELECT `password` FROM `auth` WHERE `email`='$email'");
			if($query->getNumRows() <= 0)
				return false;
			$result = $query->getResultArray();
			if(password_verify($password, $result[0]['password']))
				return true;
			return false;
		}

		public function getUsername($email)
		{
			$query = $this->db->query("SELECT `username` FROM `auth` WHERE `email`='$email'");
			$result = $query->getResultArray();
			return $result[0]['username'];
		}

		public function getUID($email)
		{
			$query = $this->db->query("SELECT `uid` FROM `auth` WHERE `email`='$email'");
			$result = $query->getResultArray();
			return $result[0]['uid'];
		}
	}