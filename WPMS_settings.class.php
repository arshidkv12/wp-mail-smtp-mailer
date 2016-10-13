<?php 

if (!defined( 'ABSPATH')) exit;
 
/**
* Admin option page
*/
class WPMS_settings 
{
	public $dir_path;

	function __construct($dir_path){

		$this->dir_path = $dir_path;

		add_action( 'admin_menu', array($this,'WPMS_admin_settings') );
	}

	function WPMS_admin_settings() {

		add_options_page( __('WP Mail Smtp Mailer Options','wp-mail-smtp-mailer'), __('Mail Smtp Mailer','wp-mail-smtp-mailer'), 'manage_options', 
			'wp-mail-smtp-mailer', array($this,'WPMS_mail_insert_options') );

		if (strpos($_SERVER['REQUEST_URI'], 'wp-mail-smtp-mailer') !== false) {

			add_action( 'admin_enqueue_scripts',  array($this,'WPMS_mail_admin_enqueue') );
		}

	}

	function WPMS_mail_insert_options() {
		if ( !current_user_can( 'activate_plugins' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-mail-smtp-mailer' ) );
		}

		$encryption = new WPMS_encryption($this->dir_path);

		$nonce 		= wp_create_nonce( 'WPMS-mail-nonce' );

		if( isset($_POST['to_email']) ){

			$nonce_vrfy = $_REQUEST['_wpnonce'];
            if ( wp_verify_nonce( $nonce_vrfy, 'WPMS-mail-nonce') ){

            	$to      = esc_sql( $_POST['to_email'] );
				$subject = 'WP Mail Smtp Mailer Test Mail';
				$body    = esc_sql( $_POST['email_body'] );
				$headers = array('Content-Type: text/html; charset=UTF-8');
				 
				
				$sent = wp_mail( $to, $subject, $body, $headers );

				global $wpms_error;

				if( $sent ){

			    	echo "<div class='notice notice-success'><p> Message sent..!</p></div>";

			    }else{

			    	$errorInfo = $GLOBALS['phpmailer']->ErrorInfo;
			    	echo "<div class='notice notice-error'><p>";
			    	if( strrpos($errorInfo, 'SMTP connect() failed') !== false ){

			    		$errorInfo = 'SMTP Error: Could not authenticate..! ';
			    	}
			    	echo $errorInfo;
			    	echo "</p></div>";
			    }


            }
		}

		if( isset($_POST['host']) ){

			$nonce_vrfy = $_REQUEST['_wpnonce'];
            if ( wp_verify_nonce( $nonce_vrfy, 'WPMS-mail-nonce')){
            	

				if( isset($_POST['encrypt']) && ( $_POST['encrypt'] == true) ){
					 
					$host 		 = esc_sql($_POST['host']);
					$port 		 = esc_sql($_POST['port']);
					$username 	 = esc_sql($_POST['username']);
					$password 	 = esc_sql($_POST['password']);
					$SMTPSecure  = esc_sql($_POST['SMTPSecure']);
					$From  		 = esc_sql($_POST['From']);
					$FromName  	 = esc_sql($_POST['FromName']);
					$encrypt     = '1';

					$file = $this->dir_path.'/salt.php';

					if ( file_exists($file) ) {

						require_once $file;


						$host 		 = $encryption->data_encrypt($host,$WPMS_salt);
						$username 	 = $encryption->data_encrypt($username,$WPMS_salt);
						$password 	 = $encryption->data_encrypt($password,$WPMS_salt);
						
						$data = array( 'host' => $host,
						 'port'=> $port, 'username' => $username, 'password' => $password,
						 'SMTPSecure' => $SMTPSecure, 'From' => $From, 'FromName' => $FromName,
						 'encrypt' => $encrypt 
						); 

						update_option('WPMS_mail_data',$data);

					}else{

						echo "<br/><div class='notice notice-error'><p>Change plugin directory permissions to 755 and deactivate plugin then activate it again ..!</p></div>";
					}



				}else{
 
					$host 		 = esc_sql($_POST['host']);
					$port 		 = esc_sql($_POST['port']);
					$username 	 = esc_sql($_POST['username']);
					$password 	 = esc_sql($_POST['password']);
					$SMTPSecure  = esc_sql($_POST['SMTPSecure']);
					$From  		 = esc_sql($_POST['From']);
					$FromName  	 = esc_sql($_POST['FromName']);
					$encrypt     = '0';

					$data = array( 'host' => $host,
					 'port'=> $port, 'username' => $username, 'password' => $password,
					 'SMTPSecure' => $SMTPSecure, 'From' => $From, 'FromName' => $FromName,
					 'encrypt' => $encrypt 
					); 

					update_option('WPMS_mail_data',$data);

				}
			}
		}

		$option_data = get_option('WPMS_mail_data','');

		if ( $option_data == '' ) {

			$data = array( 'host' => '',
			 'port'=> '', 'username' => '', 'password' => '',
			 'SMTPSecure' => '', 'From' => '', 'FromName' => '',
			 'encrypt' => '1' 
			); 
			update_option('WPMS_mail_data',$data);
			$option_data = get_option('WPMS_mail_data','');

		}



		?>


		<h2>WP Mail Smtp Mailer</h2>
 		<form action='options-general.php?page=wp-mail-smtp-mailer&_wpnonce=<?php echo $nonce; ?>' method="POST"> 
		<table class="form-table">
			<tbody>
			
				<tr valign="top">
				<th><lable>Host</lable></th>
				<td scop="row">
				<input name="host" type="text" placeholder='smtp.example.com' value="<?php  echo $option_data['host'];?>" class="host" required> 
				</td>
				<td><div class='host-info'></div></td>
				</tr>
				<tr valign="top">
				<th><lable>Port</lable></th>
				<td scop="row">
				<input name="port" type="number" placeholder='25' value="<?php  echo $option_data['port'];?>" required> 
				</td>
				</tr>
				<tr valign="top">
				<th><lable>Username</lable></th>
				<td scop="row">
				<input name="username" type="text" placeholder='Your username' value="<?php  echo $option_data['username'];?>" required> 
				</td>
				</tr>
				<tr valign="top">
				<th><lable>Password</lable></th>
				<td scop="row">
				<input name="password" type="password" placeholder='Your password' value="<?php  echo $option_data['password'];?>" required> 
				</td>
				</tr>
				
				<tr valign="top">
				<th><lable>Choose SSL or TLS, if necessary for your server</lable></th>
				<td scop="row">
				<select name='SMTPSecure'>

				<?php  
				if($option_data['SMTPSecure']=='tls'){
					echo "<option value='none'>None</option>";
					echo "<option value='ssl' >SSL</option>";
					echo "<option value='tls' selected='selected'>TLS</option>;?>";
				}elseif($option_data['SMTPSecure']=='ssl'){
					echo "<option value='ssl' selected='selected'>SSL</option>";
					echo "<option value='none'>None</option>";
					echo "<option value='tls' >TLS</option>;?>";
				}else{
					echo "<option value='none'>None</option>";
					echo "<option value='ssl' >SSL</option>";
					echo "<option value='tls' >TLS</option>;?>";
				}
				?>
				
					
				</select>

				</td>
				</tr>
				<tr valign="top">
				<th><lable>From</lable></th>
				<td scop="row">
				<input name="From" type="email"  placeholder='you@yourdomail.com' value="<?php  echo $option_data['From'];?>" /> 
				</td>
				</tr>
				<tr valign="top">
				<th><lable>From Name</lable></th>
				<td scop="row">
				<input name="FromName"   type="text" placeholder='Your Name'  value="<?php  echo $option_data['FromName'];?>"" /> 
				</td>
				</tr>
				<tr valign="top">
				<th><lable>Encrypt</lable></th>
				<td scop="row">
				<input name="encrypt" class='encrypt' type="checkbox" <?php  echo $option_data['encrypt']=='1'?'checked="checked";':''; ?>  /> 
				</td>
				</tr>
				<tr valign="top">
				 
				<td scop="row">

				</td>
				</tr>
			</tbody> 
		</table>
		<input name="submit" class='button button-primary' type="submit" value="Submit" > 
		<div  class='button button-primary' id="resetVal">Reset values</div>
		<div  class='button button-primary' id="testEmail">Test Email</div>
		</form>
	 
		<div class="testEmail-wrap">
		<form action='options-general.php?page=wp-mail-smtp-mailer&_wpnonce=<?php echo $nonce; ?>' method="POST"> 
		<table class="form-table">
			<tbody>
			
				<tr valign="top">
				<th><lable>To Email</lable></th>
				<td scop="row">
				<input name="to_email" type="email" placeholder='youremail@example.com'  required>  
				</td>
				</tr>
				<tr valign="top">
				<th><lable>Body</lable></th>
				<td scop="row">
				<textarea name="email_body" rows="6" type="text" placeholder='Your text email here...!'  required></textarea>
				</td>
				</tr>
			</tbody>
		</table>
		<input  class='button button-primary' type="submit" value="Send"> 
		</form>
		</div>

		<?php 
	}

	function WPMS_mail_admin_enqueue(){

		wp_enqueue_style( 'WPMS_mail_admin_style',plugin_dir_url( __FILE__ )  . 'admin.css');

		
		wp_register_script( 'WPMS_admin_mail', plugin_dir_url( __FILE__ )  .'admin.js',array('jquery'),'1.0', true);

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'WPMS_admin_mail' );

	}

}



?>