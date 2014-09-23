<?php
/**
 * 【Dev&UT_Guide 开发与单测示例之Model开发 以及你所需要知道的细节】
 *
 * 基于现有MVC框架的Model开发与单测三原则：
 * 1. 所有依赖的资源型对象（Dao、RPC）必须抽出并在getData()中初始化。
 * 2. 非参数获取、返回构造的逻辑，全部从getData()中抽出为public成员函数，细化并进行单元测试。
 * 3. 不要对getData()进行单元测试。原因是：getData()会初始化资源型对象，所以你无法对这些对象进行Mock，也就没有单测的意义。
 *    *注意* 所以，getData()逻辑要简单，并由你的“接口测试”来保证逻辑的正确性。
 *
 * @author  pandaoknight
 * @date    2013-9-7
 */
//require_once(PHP_ROOT . 'libs/util/Utility.php');  // [规范] 如果你需要向libs中添加代码，你发现很多代码是已有的。不要管以前的代码，但要为你新写的代码添加单元测试和注释。
require_once(PHP_ROOT . 'libs/util/HttpRequestHelper.php');
require_once(WEB_ROOT . 'models/extra/ErrorMsg.php');  // [规范] 每个项目的Return Code统一定义在这个文件中。见后续使用示例。
require_once(WEB_ROOT . 'models/extra/Enums.php');
//require_once(WEB_ROOT . 'models/extra/Model.php');
require_once(WEB_ROOT . 'models/SsoUtil.php');  // [规范] 需要共用的代码，如CheckSign()检查校验值，抽出到公共的类中。并且不要在本类的UT中测试。
require_once(WEB_ROOT . '/../dao/UserInfoDao.php');
// TODO(chenxing9): 待讨论
// *注意* 对于Utility.php、Model.php等会间接引用Session等系统资源的文件，需要将其引用抽取到Config.php中。否则单元测试无法初始化。

/**
 * 根据用户标识（UID、手机）获取用户信息
 *
 * @author chenxing9@wanda.cn
 * @date 2013-9-7
 */
class GetUserInfoModel extends Model {
  const TYPE_UID = 1;   // [规范] 常量用const定义，代码中不可出现未定义常量的数字。
  const TYPE_PHONE = 2;

  protected $userinfodao_ = null;  // [规范] 需要使用到的Dao、RPC等资源型对象必须定义为成员变量；并提供Set()方法。这样便于单测时Mock这些对象。

  /**
   * @override
   * [规范] getData()是程序的主入口，负责处理入参（GET、POST参数）以及构造Response并返回。
   */
  public function getData() {
    $value = trim(HttpRequestHelper::GetParam('value'));
    $type = trim(HttpRequestHelper::GetParam('type'));
    $sign = trim(HttpRequestHelper::GetParam('sign'));

    $value = 'u13241234';
    $type = 1;

    // [规范] 无论程序是否会用到，或者不会立刻要到，都在初始化依赖资源
    $this->SetUserInfoDao(UserInfoDao::GetClient());  // *注意* Dao的规范使用它函数：GetUserInfoFields()

    $response = new Response();

    if (!SsoUtil::CheckSign($value, $sign)) {  // [规范] 外部方法不在本类的UT中测试，对于异常情况用ErrorMsg类填充返回值并记一笔日志。
      ErrorMsg::FillResponseAndLog($response, ErrorMsg::PARAM_ERROR);
      // TODO(chenxing9): 待讨论
      // Log::Warn("Check sign failed. value:[$value] sign:[$sign]");  // *注意* 日志主要是为了快速发现问题，将关键参数记入，是右侧推荐的写法。
      return $response;
    }

    if (!$this->CheckType($type)) {  // [规范] 内部的成员方法。视逻辑分支数量需要至少写正、反2个以上的单元测试Case。
      ErrorMsg::FillResponseAndLog($response, ErrorMsg::PARAM_ERROR);
      // Log::Warn("Unkown type:[$type]");
      return $response;
    }

    $fields = $this->GetUserInfoFields($value, $type);
    if (empty($fields)) {
      ErrorMsg::FillResponseAndLog($response, ErrorMsg::USER_NOT_EXIST);
      // Log::Warn("User not exist, value:[$value] type:[$type]");
      return $response;
    }

    $response->data = array( // *注意* 返回的构造需要满足文档的约定，需要自己靠接口测试来保证其正确性。
                             //        不要对其进行单元测试，也因为构造是放在getData()中的。
      'userinfo' => $fields,
    );
    return $response;
  }

  // [规范] 这是将，抽取的原因有二：
  //        1. 抽取了“根据$type判断参数类型，然后调用响应的Dao方法获取数据”的主要逻辑
  //        2. 它封装了对资源型对象的调用，只有当你测试这类函数时才需要Mock资源型对象，并调用Set()
  //
  //        该例中，你需要测试：
  //        1. 正确的$type和$value 2个Case。
  //        2. 异常的$value 2个Case。
  //        *注意* $type已经在CheckType中保证，所以没有必要验证。
  //        单测详见 WEB_ROOT . 'unittest/models/GetUserInfoModelTest.php'。
  //
  // [规范] 关于Dao的规范和说明：
  //        我们不对Dao进行单元测试。但是Dao的行为和返回值应该是有约定的。
  //        本例是一个很好的例子，当$value是合法值但数据库中并不存在该用户时，
  //        Dao应该返回空Array()而非false或null。而这会影响你程序逻辑，所以，
  //        你需要知晓Dao的返回的约定。
  //        如果你Mock这个资源类时使用了不符合约定的假设，那么你的Mock就是错误的。
  public function GetUserInfoFields($value, $type) {
    $fields = null;
    switch ($type) {
      case self::TYPE_UID:
        $fields = $this->userinfodao_->GetUserInfoByUid($value);
        break;
      case self::TYPE_PHONE:
        $fields = $this->userinfodao_->GetUserInfoByPhone($value);
        break;
      default:
        Log::Error("Unknown Type:[$type]");
        return false;  // TODO(chenxing9): 待讨论。
    }
    return $fields;
  }

  // [规范] 抽出的成员函数。需要对其进行单测。
  public function CheckType($type) {
    $types = array(self::TYPE_UID, self::TYPE_PHONE);
    return in_array($type, $types);
  }

  // [规范] 用于“注入”Mock的资源型对象的Set()方法。
  // 1. 不要做单元测试。
  // 2. 统一放在一个类的最底部。
  public function SetUserInfoDao($dao) {
    $this->userinfodao_ = $dao;
  }
}

