<?php

namespace ProcessWire;

class LoginLogoutModule extends WireData implements Module, ConfigurableModule
{

  public static function getModuleInfo()
  {
    return array(
      'title' => 'Login/Logout Module',
      'version' => 101,
      'summary' => 'Provides login/logout functionality for templates.',
      'author' => 'AlexRdz - tech-mex.io - alexrdz.me',
      'singular' => true,
      'autoload' => true,
    );
  }

  public function init()
  {
    $this->addHookBefore('Pages::render', $this, 'handleLoginLogout');
  }

  public function handleLoginLogout(HookEvent $event)
  {
    $page = $event->object;

    if ($page->template == 'login') {
      $this->handleLogin();
    } elseif ($page->template == 'logout') {
      $this->handleLogout();
    }
  }

  protected function handleLogin()
  {
    $user = $this->wire('user');
    $input = $this->wire('input');
    $session = $this->wire('session');

    if ($user->isLoggedin()) {
      $session->redirect('/');
    }

    if ($input->post->username && $input->post->password) {
      $username = $input->post->username;
      $password = $input->post->password;

      if ($session->login($username, $password)) {
        $session->redirect('/');
      } else {
        $this->error = "Invalid username or password";
      }
    }
  }

  protected function handleLogout()
  {
    $session = $this->wire('session');
    $session->logout();
    $session->redirect('/');
  }

  public function ___install()
  {
    $this->createLoginTemplate();
    $this->createLogoutTemplate();
  }

  protected function createLoginTemplate()
  {
    $templates = $this->wire('templates');
    $fields = $this->wire('fields');

    if (!$templates->get('login')) {
      $fg = new Fieldgroup();
      $fg->name = 'login';
      $fg->add($fields->get('title'));
      $fg->save();

      $t = new Template();
      $t->name = 'login';
      $t->label = 'Login';
      $t->fieldgroup = $fg;
      $t->filename = 'login.php';
      $t->save();

      $loginFile = $this->config->paths->templates . 'login.php';
      if (!file_exists($loginFile)) {
        $template_file = fopen($loginFile, "w") or die("Unable to open file!");
        $txt = $this->getLoginTemplateContent();
        fwrite($template_file, $txt);
        fclose($template_file);
        // file_put_contents($loginFile, $this->getLoginTemplateContent());
      }
    }

    $this->createLoginPage();
  }

  protected function createLogoutTemplate()
  {
    $templates = $this->wire('templates');
    $fields = $this->wire('fields');

    if (!$templates->get('logout')) {
      $fg = new Fieldgroup();
      $fg->name = 'logout';
      $fg->add($fields->get('title'));
      $fg->save();

      $t = new Template();
      $t->name = 'logout';
      $t->label = 'Logout';
      $t->fieldgroup = $fg;
      $t->filename = 'logout.php';
      $t->save();

      $logoutFile = $this->config->paths->templates . 'logout.php';
      if (!file_exists($logoutFile)) {
        $template_file = fopen($logoutFile, "w") or die("Unable to open file!");
        $content = $this->getLogoutTemplateContent();
        fwrite($template_file, $content);
        fclose($template_file);
        // file_put_contents($logoutFile, $this->getLogoutTemplateContent());
      }
    }

    $this->createLogoutPage();
  }

  protected function createLoginPage()
  {
    $pages = $this->wire('pages');
    if (!$pages->find('template=login')->count()) {
      $p = new Page();
      $p->template = 'login';
      $p->parent = $pages->get('/');
      $p->name = 'login';
      $p->title = 'Login';
      $p->status = Page::statusHidden;
      $p->save();
    }
  }

  protected function createLogoutPage()
  {
    $pages = $this->wire('pages');
    if (!$pages->find('template=logout')->count()) {
      $p = new Page();
      $p->template = 'logout';
      $p->parent = $pages->get('/');
      $p->name = 'logout';
      $p->title = 'Logout';
      $p->status = Page::statusHidden;
      $p->save();
    }
  }

  protected function getLoginTemplateContent()
  {
    return <<<'EOT'
<?php namespace ProcessWire; ?>
<div id="content">
  <?php
  if ($user->isLoggedin()) {
      $session->redirect('/');
  }
  // create form
  $form = $modules->get('InputfieldForm');
  $form->action = "./";
  $form->method = "post";
  // username
  $username = $modules->get('InputfieldText');
  $username->label = 'Username';
  $username->attr('id+name', 'username');
  $username->required = true;
  $form->add($username);
  // password
  $password = $modules->get('InputfieldText');
  $password->label = 'Password';
  $password->attr('id+name+type', 'password');
  $password->required = true;
  $form->add($password);
  // submit button
  $submit = $modules->get('InputfieldSubmit');
  $submit->attr('name', 'submit');
  $submit->attr('value', 'Login');
  $form->add($submit);
  // check with both $_SERVER and pw api - pw api not working locally
  if ($_SERVER['REQUEST_METHOD'] === 'POST' || $input->post->submit) {
      $usernameValue = $input->post->username;
      $passwordValue = $input->post->password;
      // log user in
      $loggedIn = $session->login($usernameValue, $passwordValue);
      if ($loggedIn) {
          // redirect accordingly
          if ($user->hasRole('admin') || $user->hasRole('staff')) {
              $session->redirect('/clients/');
          } elseif ($user->hasRole('client')) {
              $session->redirect("/client/{$user->name}/");
          } else {
              $session->redirect('/');
          }
      } else {
          $error = "Invalid login credentials.";
      }
  }
  // render the form
  echo $form->render();
  if (isset($error)) {
    // update this once styles/desgn is done
      echo "<p style='color:red;'>$error</p>";
  }
  ?>
</div>
EOT;
  }

  protected function getLogoutTemplateContent()
  {
    return <<<'EOT'
<?php namespace ProcessWire;
if ($user->isLoggedin()) {
  $session->logout();
  $session->redirect('/');
} else {
  $session->redirect('/');
}
EOT;
  }

  public function getLoginLogoutLink()
  {
    $user = $this->wire('user');
    $pages = $this->wire('pages');

    if ($user->isLoggedin()) {
      return '<a href="' . $pages->get('template=logout')->url . '">Logout</a>';
    } else {
      return '<a href="' . $pages->get('template=login')->url . '">Login</a>';
    }
  }
}
