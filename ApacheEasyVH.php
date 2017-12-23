<?php
  /*
  **************************************************************************
  *                                                                        *
  * Desenvolvido por João Artur - www.joaoartur.com - github.com/JoaoArtur *
  * AnhaxTeam@2017 - www.anhax.team - github.com/anhaxteam                 *
  *                                                                        *
  **************************************************************************
  *                     Apache Easy Virtual Hosts v2.0                     *
  *          This script is for creating apache virtualhosts fast.         *
  **************************************************************************
  */

  class EasyVH {
    public function __construct() {
      system('clear');
      $inicial = "\033[31mCoded by João Artur - github.com/JoaoArtur\nAll rights for @anhaxteam\n\n\033[32mApache Easy Virtual Hosts\n\n\033[0m";
      echo $inicial;

      if (posix_getuid() == 0) {
        echo "\033[32m";
        echo "1. Ubuntu / Debian\n";
        echo "2. Fedora\n";
        echo "\033[32m3. Arch Linux \033[1m(em breve)\033[0m\n";
        echo "\033[0m";
        $this->definirCampo("\033[31mEscolha seu sistema operacional",'os');
        echo "\033[0m";

        system('clear');
        echo $inicial;
        $this->definirCampo("Domínio",'dominio');

        system('clear');
        echo $inicial;
        $this->definirCampo("Configurar banco de dados? (S/N)",'bancodedados');
        if (strtoupper($this->bancodedados) == 'S') {
          echo "::: Configurações do banco de dados :::\n";
          $this->definirCampo("IP","db_host");
          $this->definirCampo("Usuário","db_usuario");
          $this->definirCampo("Senha","db_senha");
          $this->definirCampo("Nome do banco","db_nome");
        }

        system('clear');
        echo $inicial;
        $this->definirCampo("Criar novo usuário? (S/N)","criarusuario");
        if (strtoupper($this->criarusuario) == 'S') {
          echo "::: Configurações do usuário :::\n";
          $this->definirCampo("Usuário (sem espaço)","usuario");
          $this->definirCampo("Senha","senha");
        } else {
          system('clear');
          echo $inicial;
          $this->definirCampo("Diretório do site","diretorio");
        }

        system('clear');
        echo $inicial;
        echo "\n\033[31m::: Criando virtualhost :::\n";
        switch ($this->os) {
          case 1:
            if (!file_exists('/etc/init.d/apache2')) {
              echo "\033[32mAparentemente você não está usando nenhuma distribuição baseada em Ubuntu ou Debian\n\033[0m";
            } else {
              $this->verificarUbuntuDebian();
            }
            break;
          case 2:
            if (!file_exists('/etc/init.d/httpd')) {
              echo "\033[32mAparentemente você não está usando nenhuma distribuição baseada em Fedora\n\033[0m";
            } else {
              $this->verificarFedora();
            }
            break;
          default:
          echo "\033[32mDesculpe, este sistema operacional não é suportado ainda.\n\033[0m";
            break;
        }
      } else {
        echo "Você deve executar como root!\n";
      }
    }

    public function verificarFedora() {
      $dominio = str_replace(['www.','http','https',' ','::/'],'',$this->dominio);
      if (strtoupper($this->bancodedados) == 'S') {
        try {
          $con = new PDO("mysql:host=".$this->db_host.";dbname=information_schema",$this->db_usuario,$this->db_senha);
          $con->query("CREATE database ".str_replace(' ','',$this->db_nome));
          echo "- Banco de dados (".$this->db_nome.") criado\n";
        } catch (Exception $e) {
          echo "Não foi possível criar o banco de dados (verifique usuário,senha e endereço).\n";
        }
      }
      if (strtoupper($this->criarusuario) == 'S') {
        $usuario = str_replace(' ','',strtolower($this->usuario));
        shell_exec('useradd -m -p '.$this->senha.' '.$usuario);
        echo "- Usuário ($usuario) criado\n";
        mkdir("/home/$usuario/public_html");
        mkdir("/home/$usuario/logs");
        echo "- Diretórios criados\n";
        echo "-> /home/$usuario/public_html\n";
        echo "-> /home/$usuario/logs\n";

        $publico = "/home/$usuario/public_html";
        $logs    = "/home/$usuario/logs";
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
          echo "-> $diretorio/public_html\n";
          echo "-> $diretorio/logs\n";
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

      file_put_contents("/etc/httpd/conf/httpd.conf",$config.file_get_contents("/etc/httpd/conf/httpd.conf"));
      shell_exec("httpd -S");
      shell_exec("/etc/init.d/httpd restart");
      shell_exec("echo 127.0.0.1  $dominio >> /etc/hosts");
      shell_exec("echo 'Hospedagem virtual criada usando Apache EasyVH<br>Desenvolvido por Joao Artur (github.com/JoaoArtur)<br>anhaxteam (github.com/anhaxteam)' >> $publico/index.html");
      shell_exec("chmod -R 755 $publico");
      shell_exec("chmod -R 755 $logs");
      shell_exec("chown -hR $usuario:$usuario $publico");
      shell_exec("chown -hR $usuario:$usuario $logs");

      echo "- Virtualhost (http://$dominio/) criada com sucesso :)\n";
    }
    public function verificarUbuntuDebian() {
      $dominio = str_replace(['www.','http','https',' ','::/'],'',$this->dominio);
      if (strtoupper($this->bancodedados) == 'S') {
        try {
          $con = new PDO("mysql:host=".$this->db_host.";dbname=information_schema",$this->db_usuario,$this->db_senha);
          $con->query("CREATE database ".str_replace(' ','',$this->db_nome));
          echo "- Banco de dados (".$this->db_nome.") criado\n";
        } catch (Exception $e) {
          echo "Não foi possível criar o banco de dados (verifique usuário,senha e endereço).\n";
        }
      }
      if (strtoupper($this->criarusuario) == 'S') {
        $usuario = str_replace(' ','',strtolower($this->usuario));
        shell_exec('useradd -m -p '.$this->senha.' '.$usuario);
        echo "- Usuário ($usuario) criado\n";
        mkdir("/home/$usuario/public_html");
        mkdir("/home/$usuario/logs");
        echo "- Diretórios criados\n";
        echo "-> /home/$usuario/public_html\n";
        echo "-> /home/$usuario/logs\n";

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
          echo "-> $diretorio/public_html\n";
          echo "-> $diretorio/logs\n";
          $dir   = [];
          $dir[] = "<Directory $diretorio>\n";
          $dir[] = "  Options Indexes FollowSymLinks\n";
          $dir[] = "  AllowOverride None\n";
          $dir[] = "  Require all granted\n";
          $dir[] = "</Directory>\n";
          foreach ($dir as $key => $value) {
            shell_exec("echo '$value' >> /etc/apache2/apache2.conf");
          }
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
      shell_exec("chmod -R 755 $publico");
      shell_exec("chmod -R 755 $logs");
      shell_exec("chown -hR $usuario:$usuario $publico");
      shell_exec("chown -hR $usuario:$usuario $logs");
      echo "- Virtualhost (http://$dominio/) criada com sucesso :)\n";
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
