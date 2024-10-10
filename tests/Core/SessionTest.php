<?php

use PHPUnit\Framework\TestCase;
use App\Core\ {
    Session,
    ServiceContainer
};

use App\Session\SessionManager;

class AdvancedSessionTest extends TestCase
{
    protected $session;
    protected $container;

    protected function setUp(): void
    {
        // initialize container 
        $this->container = new ServiceContainer();

        $session_config = [
            'driver' => 'array',
            'session_path' => __DIR__ . '/../storage/framework/sessions',
        ];

        $session_manager = new SessionManager($session_config, $this->container);

        // Get the session driver from the session manager
        $this->session = new Session($session_manager->driver());
    }

    public function testSetAndGetWithComplexData()
    {
        $data = [
            'user' => [
                'id' => 10,
                'name' => 'John Smith',
                'roles' => ['admin', 'manager']
            ],
            'preferences' => [
                'notifications' => false,
                'language' => 'en'
            ]
        ];

        // Set complex data in the session
        $this->session->set('user_data', $data);

        // Assert that the data was stored correctly
        $this->assertEquals($data, $this->session->get('user_data'));

        // Assert that nested values can be accessed
        $this->assertEquals('John Smith', $this->session->get('user_data.user.name'));
        $this->assertEquals(['admin', 'manager'], $this->session->get('user_data.user.roles'));
    }

    public function testSetOverwritesData()
    {
        // Set initial data
        $this->session->set('user_data', ['user' => ['id' => 1, 'name' => 'John Doe']]);
        
        // Overwrite the existing data with new data
        $this->session->set('user_data', ['user' => ['id' => 2, 'name' => 'Jane Doe']]);

        // Assert that the data was overwritten
        $this->assertEquals(['user' => ['id' => 2, 'name' => 'Jane Doe']], $this->session->get('user_data'));
    }

    public function testSetNullNestedValue()
    {
        $data = [
            'user' => [
                'id' => 10,
                'name' => 'John Smith',
                'roles' => ['admin', 'manager']
            ],
            'preferences' => [
                'notifications' => false,
                'language' => 'en'
            ]
        ];

        // Set complex data in the session
        $this->session->set('user_data', $data);

        // Remove the nested 'notifications' key
        $this->session->set('user_data.preferences.notifications', null);
        
        $expectedData = [
            'user' => [
                'id' => 10,
                'name' => 'John Smith',
                'roles' => ['admin', 'manager']
            ],
            'preferences' => [
                'notifications' => null,
                'language' => 'en'
            ]
        ];

        // Assert that the 'notifications' key was removed but the rest remains intact
        $this->assertEquals($expectedData, $this->session->get('user_data'));

        $this->session->save();
    }

    public function testClearSession()
    {
        // Set multiple values in session
        $this->session->set('user_data', ['user' => ['id' => 1, 'name' => 'John']]);
        $this->session->set('settings', ['theme' => 'dark', 'notifications' => true]);

        // Clear the session
        $this->session->clear();

        // Assert that the session is empty
        $this->assertNull($this->session->get('user_data'));
        $this->assertNull($this->session->get('settings'));
    }

    public function testRemoveNonExistentKey()
    {
        // Try to remove a key that doesn't exist
        $this->session->remove('non_existent_key');

        // Ensure that removing a non-existent key does not cause any issues
        $this->assertNull($this->session->get('non_existent_key'));
    }

    public function testSessionHandlesEmptyValues()
    {
        // Set an empty value
        $this->session->set('empty_key', '');

        // Assert that the empty value is handled correctly
        $this->assertEquals('', $this->session->get('empty_key'));

        // Set a null value
        $this->session->set('null_key', null);

        // Assert that null values are handled and removed
        $this->assertNull($this->session->get('null_key'));
    }

    public function testSessionGetNullValue()
    {
        // Assert that a non-existent key returns null
        $value = $this->session->get('non_existent_key');

        $this->assertNull($value);

        // Assert that a non-existent key with a default value returns the default value
        $value = $this->session->get('non_existent_key', 'default_value');

        $this->assertEquals('default_value', $value);

        // Assert that a key with a null value returns null
        $this->session->set('null_key', null);

        $value = $this->session->get('null_key');
        $this->assertNull($value);
    }

    public function testUpdateArrayData()
    {
        // Set initial session data
        $data = [
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'roles' => ['editor']
            ]
        ];

        $this->session->set('user_data', $data);

        // Update the session data by adding a role
        $data['user']['roles'][] = 'admin';
        $this->session->set('user_data', $data);

        // Assert that the session data was updated correctly
        $this->assertEquals(['editor', 'admin'], $this->session->get('user_data.user.roles'));
    }

    public function testSessionOverrideArray()
    {
        // Set initial data
        $this->session->set('user_data', ['user' => ['id' => 1, 'name' => 'John Doe']]);
        
        // Attempt to merge additional data into the session without overwriting
        $this->session->set('user_data', ['user' => ['roles' => ['editor']]]);

        // Expected result should merge both datasets
        $expectedData = [
            'user' => [
                'roles' => ['editor']
            ]
        ];

        // Assert the merged result
        $this->assertEquals($expectedData, $this->session->get('user_data'));
    }

    public function testRemoveArrayValue()
    {
        // Set initial data
        $this->session->set('user_data', ['user' => ['id' => 1, 'name' => 'John Doe', 'roles' => ['editor', 'admin']]]);

        // Remove a value from the roles array
        $this->session->remove('user_data.user.roles.0');

        // Assert that the value was removed
        $this->assertEquals(['admin'], $this->session->get('user_data.user.roles'));
    }

    public function testRemoveArrayKey()
    {
        // Set initial data
        $this->session->set('user_data', ['user' => ['id' => 1, 'name' => 'John Doe', 'roles' => ['editor', 'admin']]]);

        // Remove the roles key
        $this->session->remove('user_data.user.roles');

        // Assert that the key was removed
        $this->assertNull($this->session->get('user_data.user.roles'));
    }

    public function testRemoveNestedArrayKey()
    {
        // Set initial data
        $this->session->set('user_data', ['user' => ['id' => 1, 'name' => 'John Doe', 'roles' => ['editor', 'admin']]]);

        // Remove the user key
        $this->session->remove('user_data.user');

        // Assert that the key was removed
        $this->assertNull($this->session->get('user_data.user'));
    }

    public function testRemoveArrayKeyWithNullValue()
    {
        // Set initial data
        $this->session->set('user_data', ['user' => ['id' => 1, 'name' => 'John Doe', 'roles' => ['editor', 'admin']]]);

        // Remove the roles key
        $this->session->remove('user_data.user.roles');

        // Assert that the key was removed
        $this->assertNull($this->session->get('user_data.user.roles'));
    }
}
