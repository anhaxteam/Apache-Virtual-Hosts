<?php
  /*
  **************************************************************************
  *                                                                        *
  * Desenvolvido por João Artur - www.joaoartur.com - github.com/JoaoArtur *
  * AnhaxTeam@2017 - www.anhax.team - github.com/anhaxteam                 *
  *                                                                        *
  **************************************************************************
  *                       Apache Easy Virtual Hosts                        *
  *          This script is for creating apache virtualhosts fast.         *
  **************************************************************************
  */

  class EasyVH {
    public function __construct() {
      echo "\033[31mCoded by João Artur - github.com/JoaoArtur\nAll rights for @anhaxteam\n\n\033[32mApache Easy Virtual Hosts\n\n\033[0m";

      if (posix_getuid() == 0) {
        $this->definirCampo("Domínio",'dominio');
        $this->definirCampo("Configurar banco de dados? (S/N)",'bancodedados');
        if (strtoupper($this->bancodedados) == 'S') {
          echo "::: Configurações do banco de dados :::\n";
          $this->definirCampo("IP","db_host");
          $this->definirCampo("Usuário","db_usuario");
          $this->definirCampo("Senha","db_senha");
          $this->definirCampo("Nome do banco","db_nome");
        }
        $this->definirCampo("Criar novo usuário? (S/N)","criarusuario");
        if (strtoupper($this->criarusuario) == 'S') {
          echo "::: Configurações do usuário :::\n";
          $this->definirCampo("Usuário (sem espaço)","usuario");
          $this->definirCampo("Senha","senha");
        } else {
          $this->definirCampo("Diretório do site","diretorio");
        }

        echo "\n\033[31m::: Criando virtualhost :::\n";
        $this->verificarCampos();
      } else {
        echo "Você deve executar como root!\n";
      }
    }

    public function verificarCampos() {
      $dominio = str_replace(['www.','http','https',' ','::/'],'',$this->dominio);
      if (strtoupper($this->bancodedados) == 'S') {
        try {
          $con = new PDO("mysql:host=".$this->db_host.";dbname=information_schema",$this->db_usuario,$this->db_senha);
          $con->query("CREATE database ".str_replace(' ','',$this->db_nome));
          echo "- Banco de dados criado\n";
        } catch (Exception $e) {
          echo "Não foi possível criar o banco de dados (verifique usuário,senha e endereço).\n";
        }
      }
      if (strtoupper($this->criarusuario) == 'S') {
        $usuario = str_replace(' ','',strtolower($this->usuario));
        shell_exec('useradd -m -p '.$this->senha.' '.$usuario);
        echo "- Usuário criado\n";
        mkdir("/home/$usuario/public_html");
        mkdir("/home/$usuario/logs");
        echo "- Diretórios criados\n";

        $publico = "/home/$usuario/public_html";
        $logs    = "/home/$usuario/logs";
        $dir   = [];
        $dir[] = "<Directory /home/$usuario/>\n";
        $dir[] = "  Options Indexes FollowSymLinks\n";
        $dir[] = "  AllowOverride None\n";
        $dir[] = "  Require all granted\n";
        $dir[] = "</Directory>\n";
        foreach ($dir as $key => $value) {
          shell_exec("echo '$value' >> /etc/apache2/apache2.conf");
        }
      } else {
        $diretorio = str_replace(' ','',strtolower($this->diretorio));
        if ($diretorio != '') {
          if (file_exists($diretorio)) {} else {
            mkdir($diretorio);
          }
          $usuario = 'www-data';
          mkdir("$diretorio/public_html");
          mkdir("$diretorio/logs");
          $publico = "$diretorio/public_html";
          $logs    = "$diretorio/logs";
          echo "- Diretórios criados\n";
        } else {
          die("Diretório não especificado...\n");
        }
      }

      $config = '';
      $config.= "<VirtualHost $dominio:80>\n";
      $config.= " # Criado usando EasyVH\n";
      $config.= " ServerName $dominio\n";
      $config.= " ServerAdmin admin@$dominio\n";
      $config.= " DocumentRoot $publico\n";
      $config.= " ErrorLog $logs/error.log\n";
      $config.= " CustomLog $logs/access.log combined\n";
      $config.= "</VirtualHost>";

      file_put_contents("/etc/apache2/sites-available/$dominio.conf",$config);
      shell_exec("a2ensite $dominio");
      shell_exec("service apache2 reload");
      shell_exec("echo 127.0.0.1  $dominio >> /etc/hosts");
      shell_exec("echo 'Hospedagem virtual criada usando Apache EasyVH<br>Desenvolvido por Joao Artur (github.com/JoaoArtur)<br>anhaxteam (github.com/anhaxteam)' >> $publico/index.html");
      shell_exec("chown -hR $usuario:$usuario $publico");
      shell_exec("chown -hR $usuario:$usuario $logs");
      echo "- Virtualhost criada com sucesso :)\n";
    }

    public function definirCampo($nome,$variavel) {
      echo "$nome: \033[1m";
      $this->$variavel = fgets(STDIN);
      echo "\033[0m";
      $this->$variavel = str_replace(array("\n","\r"),'',$this->$variavel);
      return $this->$variavel;
    }
  }
  new EasyVH;

?>
