<?php

class Application_Model_SendMails_Mails
{
    /**
     * Envía un email a los destinatarios proporcionados
     * @param array $destinatarios
     * @param string $asunto
     * @param string $msj
     */
    public function sendMail($destinatarios,$cc,$asunto,$msj,$adjunto=false)
    {
        // 		if(in_array(APPLICATION_ENV, array('development','production')) ) {
    
        $params = json_encode(array($destinatarios,$cc,$asunto));
    
        try {
    
            $mail = new Zend_Mail('utf-8');
            	
            $mail->addTo($destinatarios);
            if (!empty($cc)) $mail->addCc($cc);
            	
            $mail->setSubject($asunto);
            $mail->setBodyHtml($msj, 'utf-8');
    
            if ($adjunto){
                $adjuntoNombre = basename($adjunto);
                $at = new Zend_Mime_Part(file_get_contents($adjunto));
                $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $at->encoding    = Zend_Mime::ENCODING_BASE64;
                $at->filename    = $adjuntoNombre;
                $mail->addAttachment($at);
            }
    
    
            if($mail->send()) return false;
            else return true;
    
        }catch (Zend_Exception $e){
            //  				print_r($e->getMessage());
            $params = $params. "\n### ERROR ###".json_encode($e->getMessage());
    
           // $this->logapp->setLog("SendMail - To: ".$destinatarios, 0, array( 'params'=>$params, 'user_id'=>((isset($this->user->ID))?$this->user->ID:NULL), 'username'=>((isset($this->user->username))?$this->user->username:NULL)));
    
            return false;
        }
        // 		}
    }
    
}
