<?php

  class email
  {
    public $from_name;
    public $from_email;
    public $reply_name;
    public $reply_email;
    public $type;
    public $subject;
    public $content;
    public $errors       = array();

    private $to          = array();
    private $cc          = array();
    private $bcc         = array();

    /* PUBLIC METHODS *******************************************/

    public function __construct()
    {
      $this->type  = 'text/plain';
    }

    // verifies that an email address is of the correct format
    public static function verify_email($email)
    {
      return ereg('^[A-Za-z0-9\._-]+@([A-Za-z0-9][A-Za-z0-9-]{1,62})(\.[A-Za-z][A-Za-z0-9-]{1,62})+$', $email);
    }

    public function add_to($name, $email)
    {
      if(!empty($name) and email::verify_email($email))
      {
        $this->to[] = $this->clean_for_email($name) . " <$email>";
        return TRUE;
      }

      return FALSE;
    }

    public function add_cc($name, $email)
    {
      if(!empty($name) and email::verify_email($email))
      {
        $this->cc[] = $this->clean_for_email($name) . " <$email>";
        return TRUE;
      }

      return FALSE;
    }

    public function add_bcc($name, $email)
    {
      if(!empty($name) and email::verify_email($email))
      {
        $this->bcc[] = $this->clean_for_email($name) . " <$email>";
        return TRUE;
      }

      return FALSE;
    }

    public function send()
    {
      // return FALSE if the email wont send
      if(!$this->check_email_details()){return FALSE;}

      // if you have a problem from same spam engine or hotmail use these sample headers.
      $headers = "";
      $headers .= "X-Sender:  " . $this->from_name . " <" . $this->from_email . ">\n";
      $headers .= "From: " . $this->from_name . " <" . $this->from_email . ">\n";

      // add in ccs
      if(!empty($this->cc))
      {
        foreach($this->cc as $value){$headers .= "Cc: " . $value . "\n";}
      }

      // add in bccs
      if(!empty($this->bcc))
      {
        foreach($this->bcc as $value){$headers .= "Bcc: " . $value . "\n";}
      }

      // add in reply to
      if(!empty($this->reply_name) and !empty($this->reply_email))
      {
        $headers .= "Reply-To: " . $this->reply_name . " <" . $this->reply_email . ">\n";
      }

      $headers .= "Date: ".date("r")."\n";
      $headers .= "Message-ID: <".date("YmdHis")."info@".$_SERVER['SERVER_NAME'].">\n";
      $headers .= "Subject: " . $this->subject . "\n";
      $headers .= "Return-Path: " . $this->from_name . " <" . $this->from_email . ">\n";
      $headers .= "MIME-Version: 1.0\n";
      $headers .= "Content-type: " . $this->type . ";charset=utf-8\n";

      // Send the email
      $tos = implode(', ', $this->to);
      if($this->type == 'text/plain'){$this->content = wordwrap($this->content);}
      return mail($tos, $this->subject, $this->content, $headers);
    }

    /* PRIVATE METHODS ******************************************/

    // cleans a string for use in email headers
    private function clean_for_email($string)
    {
      return eregi_replace("[^a-zA-Z0-9_\@\.\'\ \-]", "", $string);
    }

    private function check_email_details()
    {
      if(count($this->to) == 0){$this->errors[] = "No To address specified";}
      if(empty($this->subject)){$this->errors[] = "No Subject line filled in";}
      if(empty($this->content)){$this->errors[] = "No Content filled in";}
      $types = array('text/plain', 'text/html');
      if(!in_array($this->type, $types)){$this->errors[] = "Invalid type specified, must be: " . implode(', ', $types);}

      if(empty($this->from_email) or empty($this->from_name))
      {
        $this->errors[] = "Please enter a valid from address";
      }
      else
      {
        $this->from_email = $this->clean_for_email($this->from_email);
        $this->from_name  = $this->clean_for_email($this->from_name);
      }

      if(!empty($this->reply_email) and !empty($this->reply_name))
      {
        $this->reply_email = $this->clean_for_email($this->reply_email);
        $this->reply_name  = $this->clean_for_email($this->reply_name);
      }

      if(count($this->errors) > 0)
      {
        return FALSE;
      }

      return TRUE;
    }
  }

?>
