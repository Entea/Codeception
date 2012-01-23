<?php

class FrameworksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Util\Framework
     */
    protected $module;

    public function setUp() {
        $this->module = new \Codeception\Module\PhpSiteHelper();
    }

    public function tearDown() {
        data::clean();
    }
    
    public function testAmOnPage() {
        $this->module->amOnPage('/');
        $this->module->see('Welcome to test app!');

        $this->module->amOnPage('/info');
        $this->module->see('Information');
    }

    public function testSee() {
        $this->module->amOnPage('/');
        $this->module->see('Welcome to test app!');

        $this->module->amOnPage('/');
        $this->module->see('Welcome to test app!','h1');

        $this->module->amOnPage('/info');
        $this->module->see('valuable','p');
        $this->module->dontSee('Welcome');
        $this->module->dontSee('valuable','h1');
    }

    public function testSeeInCurrentUrl() {
        $this->module->amOnPage('/info');
        $this->module->seeInCurrentUrl('/info');
    }

    public function testSeeLink() {
        $this->module->amOnPage('/');
        $this->module->seeLink('More info');
        $this->module->dontSeeLink('/info');
        $this->module->dontSeeLink('#info');

        $this->module->amOnPage('/info');
        $this->module->seeLink('Back');
    }
    
    public function testClick() {
        $this->module->amOnPage('/');
        $this->module->click('More info');
        $this->module->seeInCurrentUrl('/info');
    }
    
    public function testCheckboxByCss() {
        $this->module->amOnPage('/form/checkbox');
        $this->module->checkOption('#checkin');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('agree', $form['terms']);
    }

    public function testChecxboxByLabel() {
        $this->module->amOnPage('/form/checkbox');
        $this->module->checkOption('I Agree');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('agree', $form['terms']);
    }

    public function testSelectByCss() {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('form select[name=age]','adult');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('adult', $form['age']);
    }
    
    public function testSelectByLabel() {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('Select your age','dead');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('dead', $form['age']);
    }

    public function testSelectByLabelAndOptionText() {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('Select your age','21-60');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('adult', $form['age']);
    }
    
    public function testHidden() {
        $this->module->amOnPage('/form/hidden');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('kill_people', $form['action']);
    }
    
    public function testTextareaByCss() {
        $this->module->amOnPage('/form/textarea');
        $this->module->fillField('textarea','Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['description']);
    }

    public function testTextareaByLabel() {
        $this->module->amOnPage('/form/textarea');
        $this->module->fillField('Description','Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['description']);
    }
    
    public function testTextFieldByCss() {
        $this->module->amOnPage('/form/field');
        $this->module->fillField('#name','Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['name']);
    }

    public function testTextFieldByLabel() {
        $this->module->amOnPage('/form/field');
        $this->module->fillField('Name','Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['name']);
    }

    public function testFileFieldByCss() {
        $this->module->amOnPage('/form/file');
        $this->module->attachFile('#avatar', 'app/avatar.jpg');
        $this->module->click('Submit');
        $this->assertNotEmpty(data::get('files'));
        $files = data::get('files');
        $this->assertArrayHasKey('avatar', $files);
        $this->assertEquals('avatar.jpg', $files['avatar']['name']);
    }

    public function testFileFieldByLabel() {
        $this->module->amOnPage('/form/file');
        $this->module->attachFile('Avatar', 'app/avatar.jpg');
        $this->module->click('Submit');
        $this->assertNotEmpty(data::get('files'));
    }

    public function testSeeCheckboxIsNotChecked() {
        $this->module->amOnPage('/form/checkbox');
        $this->module->dontSeeCheckboxIsChecked('#checkin');
    }
    
    public function testSeeCheckboxChecked() {
        $this->module->amOnPage('/form/complex');
        $this->module->seeCheckboxIsChecked('#checkin');
    }
    
    public function testSubmitForm() {
        $this->module->amOnPage('/form/complex');
        $this->module->submitForm('form', array('name' => 'Davert'));
        $form = data::get('form');
        $this->assertEquals('Davert', $form['name']);
        $this->assertEquals('kill_all', $form['action']);
    }
    
    public function testAjax() {
        $this->module->sendAjaxGetRequest('/info', array('show' => 'author'));
        $this->assertArrayHasKey('HTTP_X_REQUESTED_WITH', $_SERVER);
        $get = data::get('params');
        $this->assertEquals('author', $get['show']);

        $this->module->sendAjaxPostRequest('/form/complex', array('show' => 'author'));
        $this->assertArrayHasKey('HTTP_X_REQUESTED_WITH', $_SERVER);
        $post = data::get('form');
        $this->assertEquals('author', $post['show']);

    }

    public function testSeeWithUnicode() {
        $this->module->amOnPage('/info');
        $module = $this->module;
        $mockedcall = function ($args) use ($module) {
            $method = array_shift($args);
            call_user_func_array(array($module,$method), $args);
        };

        $guy = \Codeception\Util\Stub::make('Codeception\AbstractGuy', array('scenario' => \Codeception\Util\Stub::makeEmpty('Codeception\Scenario',array('assertion' => $mockedcall, 'action' => $mockedcall))));
        $guy->see('Текст');
        $guy->see('Текст', 'p');
        $guy->seeLink('Ссылочка');
        $guy->click('Ссылочка');

    }

}