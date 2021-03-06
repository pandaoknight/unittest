<?php
/**
 * 【Dev&UT_Guide 开发与单测示例之UnitTestCase编写 以及你所需要知道的细节】
 *
 * @author pandaoknight
 * @date 2013-9-7
 */
require_once(dirname(__FILE__) . '/../UnittestConfig.php');
require_once(PHP_ROOT . '/libs/thirdparty/simpletest/autorun.php');
require_once(WEB_ROOT . '/models/GetUserInfoModel.php');
require_once(WEB_ROOT . '/../dao/UserInfoDaoImpl.php');  // *注意* 这里我们直接Mock住xxxDaoImpl.php即可，因为它会包含所有的函数接口。


Mock::generate('UserInfoDaoImpl');  // *注意* Mock对象的使用有两个要点：
                                    //        1. 在Class前，引用源文件，然后向SimpleTest单侧框架注册该Mock。
                                    //        2. 在Case中，以 new Mock{对象名称} 的形式创建Mock对象。

class GetUserInfoModelTest extends UnitTestCase {
  private $model_;

  function setUp() {
    $this->model_ = new GetUserInfoModel;
  }

  function tearDown() {
  }

  // [规范] __简单的函数__
  //        测试一般直接直接用 assert( XXXfun() ) 的模式即可。
  //        Case的名字为：test{函数名字}。如下所示。
  //        Case要求写注释：包含"函数概述"、"测试包含" 2项。如下所示。
  //        因为被测代码一般有函数功能注释所以，"函数概述"不用累述，但需要
  //        包含被测函数名，以和__复杂的函数__的单测保持一致（见下文）。
  /**
   *  函数概述 CheckType()
   *  测试包含 正常流测试，边界测试
   */
  function testCheckType() {
    // [规范] 正常流
    $this->assertTrue($this->model_->CheckType(1));
    $this->assertTrue($this->model_->CheckType(2));
    // [规范] 异常流：边界测试
    $this->assertFalse($this->model_->CheckType(0));
    $this->assertFalse($this->model_->CheckType(3));
  }

  // [规范] __复杂的函数__
  //        测试一般包含 设置Mock资源、执行、结果检查 3个部分。
  //        复杂函数一般需要N条流才能覆盖逻辑分支。所以，为了让每个Case简单，需要每个流拆为一个Case。
  //        Case的名字为：test{函数名字}{补充描述}。如下所示。
  //        Case要求写注释：
  //        "函数概述" 至少写明被测函数名，这样便于快速找到多个测试同一函数的Case，也提高可读性。
  //        "测试包含" 中先用 #{编号} 写明编号。如下所示。
  //                   然后注明 正常流|异常流，然后补充说明。补充说明简明扼要就好。
  /**
   *  函数概述 GetUserInfoFields()
   *  测试包含 #1 正常流: 用UID查询用户信息
   */
  function testGetUserInfoFieldsByUid() {
    // [规范] Mock所需的资源型对象，并设定预期。
    $dao = new MockUserInfoDaoImpl();
    $dao->returns('GetUserInfoByUid', array('some'=>'what'));
    $dao->expectOnce('GetUserInfoByUid');

    // [规范] 设置资源对象并执行
    $this->model_->SetUserInfoDao($dao);
    $result = $this->model_->GetUserInfoFields('u1234567890', GetUserInfoModel::TYPE_UID);

    // [规范] 结果检查
    $this->assertNotNull($result);
  }

  /**
   *  函数概述 GetUserInfoFields()
   *  测试包含 #2 正常流: 用手机号查询用户信息
   */
  function testGetUserInfoFieldsByPhone() {
    $dao = new MockUserInfoDaoImpl();
    $dao->returns('GetUserInfoByPhone', array('some'=>'what'));
    $dao->expectOnce('GetUserInfoByPhone');

    $this->model_->SetUserInfoDao($dao);
    $result = $this->model_->GetUserInfoFields('13800138000', GetUserInfoModel::TYPE_PHONE);

    $this->assertNotNull($result);
  }

  /**
   *  函数概述 GetUserInfoFields()
   *  测试包含 #3 异常流: 用UID查询用户信息，但Dao返回false说明用户不存在。
   */
  function testGetUserInfoFieldsByUid_user_not_exists() {
    $dao = new MockUserInfoDaoImpl();
    $dao->returns('GetUserInfoByUid', false);
    $dao->expectOnce('GetUserInfoByUid');

    $this->model_->SetUserInfoDao($dao);
    $result = $this->model_->GetUserInfoFields('u1234123', GetUserInfoModel::TYPE_UID);

    $this->assertFalse($result);
  }

  /**
   *  函数概述 GetUserInfoFields()
   *  测试包含 #4 异常流: 用手机号查询用户信息，但Dao返回false说明用户不存在。
   */
  function testGetUserInfoFieldsByPhone_user_not_exists() {
    $dao = new MockUserInfoDaoImpl();
    $dao->returns('GetUserInfoByPhone', false);
    $dao->expectOnce('GetUserInfoByPhone');

    $this->model_->SetUserInfoDao($dao);
    $result = $this->model_->GetUserInfoFields('13800138000', GetUserInfoModel::TYPE_PHONE);

    $this->assertFalse($result);
  }

  /**
   * 函数概述 GetUserInfoFields()
   * 测试包含 #5 异常流：边界测试: $type参数不在已定义的范围内。
   */
  function testGetUserInfoFields_unknown_type() {
    $this->assertFalse($this->model_->GetUserInfoFields('u1234123', 0));
    $this->assertFalse($this->model_->GetUserInfoFields('13800138000', 0));
    $this->assertFalse($this->model_->GetUserInfoFields('ABCDFSESF1234', 3))
    $this->assertFalse($this->model_->GetUserInfoFields('18600000000', 3));
  }
}
?>

