<?php
    require './Bibliotecas/PHPMailer/Exception.php';
    require './Bibliotecas/PHPMailer/OAuth.php';
    require './Bibliotecas/PHPMailer/PHPMailer.php';
    require './Bibliotecas/PHPMailer/POP3.php';
    require './Bibliotecas/PHPMailer/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception; 
    $a = 0;   

    class Mensagem {
        private $assunto = null;
        private $mensagem = null;
        private $emails = null;
        public $status = array( 'codigo_status' => null, 'descricao_status' => '');

        public function _get($atributo){
            return $this->$atributo;
        }

        public function _set($atributo, $valor){
            $this->$atributo = $valor;
        }

        public function mensagemValida(){
            if (empty($this->assunto) || empty($this->mensagem)){
                return false;
            }
            return true;
        }
    }

    $mensagem = new Mensagem();

    $mensagem->_set('assunto', $_POST['assunto']);
    $mensagem->_set('mensagem', $_POST['mensagem']);
    $mensagem->_set('emails', $_POST['emails']);

    $emails = $_POST['emails'];
      
    if(!$mensagem->mensagemValida()){
        echo 'Mensagem inválida';
        header('Location: index.php');
    } 
  
    $mail = new PHPMailer(true);

    // *********************** Conexão database ***************************//
    $dsn = 'mysql:host=localhost;dbname=registo_dos_emails';
    $usuario = 'root';
    $senha = '';

    try {
        $conexao = new PDO($dsn, $usuario, $senha); 

        $query = '
            select * from lista_emails 
        ';
        
        $query2 = '
            select  count(id) from lista_emails
        ';
       
                
        $stmt = $conexao->query($query);
        $lista = $stmt->fetchAll();    

        $numeroEmails = $conexao->query($query2);
        $qntEmails = $numeroEmails->fetchAll();                         
                               
        echo 'Lista dos Emails que foram enviados: ';?><br><?php   
        for ($x = 0; $x < $qntEmails[0][0]; $x++ ){                            
                print_r($x.'-'.$lista[$x][0]);?><br><?php     
                $a = $a + 1;            
        }      
        
        $a + 1;

        function valida_email($emails) {                                            // Verificar os e-mails válidos
            return filter_var($emails, FILTER_VALIDATE_EMAIL);
        }

        if (($emails != null) && (valida_email($emails) == true)) {                 // Verificador de e-mail  válido
            
            $query3 = "
               SELECT * FROM lista_emails WHERE emails = '{$emails}';
            ";
            
            if ($query3 != 0){
                $query4 = "
                    INSERT INTO lista_emails (emails, id) VALUES ('$emails', '$a')
                    ";
                $receberEmail = $conexao->query($query4);
            }
        }   
                    

    } catch (PDOException $e) {
        echo '<pre>';
        print_r($e);
        echo '</pre>';
    }

    // *********************** Configuração do server ***************************//

    try {

        $mail->SMTPDebug = false;                                    // Habilita o debug
        $mail->isSMTP();                                             // Envio usando SMTP
        $mail->Host       = 'smtp.gmail.com';                   
        $mail->SMTPAuth   = true;                                    // Enable SMTP authentication
        $mail->Username   = 'email@hotmail.com';                     // SMTP EMAIL - Email de Envio
        $mail->Password   = 'senhasegura123';                        // SMTP SENHA - Senha de Envio
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;          // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 587;                                    
    
       
        $mail->setFrom('email@hotmail.com', 'Email automático');     // Email e nome da pessoa que envia
        
        for ($i = 0; $i < $qntEmails[0][0]; $i++){                              // Quantidade exata de e-mails no banco de dados
            $mail->addAddress($lista[$i][0]);                                   // LOOP de envio dos EMAILS
        }       
      

        $mail->isHTML(true);                                 
        $mail->Subject = $mensagem->_get('assunto');                             // Assunto da mensagem
        $mail->Body    = $mensagem->_get('mensagem');                            // Mensagem
        $mail->AltBody = 'É necessário utilziar um client que suporte HTML.';    // Debug caso o email não suporte HTML
        
        
        //$mail->send();                                                         // Envia o e-mail
        
       

        $mensagem->status['codigo_status'] = 1;                                  // Variável que recebe o valor para deixar mais visual
        $mensagem->status['descricao_status'] = 'Mensagem enviada com sucesso';

        

    } catch (Exception $e) {
        
        $mensagem->status['codigo_status'] = 2;                                  // Variável que recebe o valor para deixar mais visual
        $mensagem->status['descricao_status'] = 'Não foi possível enviar este e-mail. Detalhes do erro:' . $mail->ErrorInfo;
    }
 
?>

<html>
    <head>
        <meta charset="utf-8" />
        <title>App Mail Send</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    </head>
    <body>            
            <div class="row">
                <div class="col-md-12">
                    
                    <?php if($mensagem->status['codigo_status'] == 1) { ?> 

                        <div class="container">
                        <h1 class="display-4 text-success">Sucesso</h1>
                        <p><?= $mensagem->status['descricao_status'] ?></p>
                        <a href="index.php" class="btn btn-success btn-lg mt-5 text-white">Voltar</a>
                        </div>

                    <?php } ?>

                    <?php if($mensagem->status['codigo_status'] == 2) { ?>
                        <div class="container">
                        <h1 class="display-4 text-danger">Ops, ocorreu algum erro</h1>
                        <p> <? $mensagem->status['descricao_status'] ?></p>
                        <a href="index.php" class="btn btn-danger btn-lg mt-5 text-white">Voltar</a>
                        </div>
                    <?php } ?>         
                </div>
            </div>
        </div>    
    </body>
</html>
