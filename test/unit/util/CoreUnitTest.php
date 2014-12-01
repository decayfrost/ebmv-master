<?php
/**
 * Test case for Utils - Core
 *
 * @package    Test
 * @subpackage Core
 * @since      2012-09-01
 * @author     lhe<helin16@gmail.com>
 *
 */
class CoreUnitTest extends CoreUtilsUnitTestAbstract
{
    /**
     * testing the Core::setUser(), Core::rmUser(), Core::getUser(), Core::getRole, Core::rmRole and Core::setRole() function
     */
    public function testSetUserNRole()
    {
        $userAccount = new UserAccount();
        $role = new Role();
        $userAccount->setId(1);
        $role->setId(1);
        
        Core::setUser($userAccount, $role);
        $this->assertEquals($userAccount, Core::getUser());
        $this->assertEquals($role, Core::getRole());
        
        Core::rmRole();
        $this->assertEquals($userAccount, Core::getUser());
        $this->assertEquals(null, Core::getRole());
        
        Core::rmUser();
        $this->assertEquals(null, Core::getUser());
        $this->assertEquals(null, Core::getRole());
        
        Core::setUser($userAccount);
        $this->assertEquals($userAccount, Core::getUser());
        $this->assertEquals(null, Core::getRole());
        
        Core::setRole($role);
        $this->assertEquals($userAccount, Core::getUser());
        $this->assertEquals($role, Core::getRole());
    }
    /**
     * testing the Core::serialize() and Core::unserialize() function
     */
    public function testSerializednUnserialized()
    {
        $userAccount = new UserAccount();
        $role = new Role();
        $userAccount->setId(1);
        $role->setId(1);
        
        Core::setUser($userAccount, $role);
        $storage = array('userAccount' => Core::getUser(), 'role' => Core::getRole());
        $serialized = Core::serialize();
        $this->assertEquals(serialize($storage), Core::serialize());
        
        Core::rmUser();
        $this->assertEquals($storage, Core::unserialize($serialized));
        $this->assertEquals($userAccount, Core::getUser());
        $this->assertEquals($role, Core::getRole());
    }
}
?>