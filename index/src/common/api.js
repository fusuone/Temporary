/**
 * 接口
 */

// import devInfo from './devInfo';
import config from './config';

const { platform } = config;

// 是否是开发模式
// const isDev = process.env.NODE_ENV === 'development';

const fixed = `p=${platform}`; // 固定参数

let baseURL;
const type = 0;
switch (type) {
  // 线上正式
  case 0:
    baseURL = `https://wolf.kassor.cn/team/background/index.php?${fixed}`;
    break;
  // 线上测试
  case 1:
    baseURL = `https://wolf.kassor.cn/team160/background/index.php?${fixed}`;
    break;
  // 本地测试
  case 2:
    baseURL = 'http://192.168.3.93/team/background/index.php?';
    // baseURL ='http://192.168.3.73/api/team/background/index.php?'
    break;
  // 其它
  default:
}

export default {
  //售后
  get_after_sale: `${baseURL}&m=hzz&a=get_after_sale`,
  // 审核k
  set_afsale_stus: `${baseURL}&m=hzz&a=set_afsale_stus`,
  //首页顶部轮播图
  get_index_ad_0: `${baseURL}&m=hzz&a=get_index_ad_0`,
  // 取得首页开始部5大图 (location=2 为最大的大图)
  get_index_ad_1: `${baseURL}&m=hzz&a=get_index_ad_1`,
  // 取得模幅
  get_index_ad_2: `${baseURL}&m=hzz&a=get_index_ad_2`,
  // 百货品牌广告
  get_index_ad_3: `${baseURL}&m=hzz&a=get_index_ad_3`,
  // 酒水品牌广告
  get_index_ad_4: `${baseURL}&m=hzz&a=get_index_ad_4`,
  // 设置首页广告内容（通用）
  set_index_ad: `${baseURL}&m=hzz&a=set_index_ad`,
  // 上传图片
  uploadimage: `${baseURL}&m=upload&a=uploadimg`,
  //批发商上架商品
  setmallwares: `${baseURL}&m=main&a=setmallwares`,
  // 商家  满减规则  查询
  get_mall_disc: `${baseURL}&m=hzz&a=get_mall_disc`,
  //设置满减
  set_mall_disc: `${baseURL}&m=hzz&a=set_mall_disc`,
  //设置满减状态
  set_mdisc_staus: `${baseURL}&m=hzz&a=set_mdisc_staus`,
  // 图片上传
  uploadimg: `${baseURL}&m=upload&a=uploadimg`,
  //获取自己发布的优惠卷
  get_mall_coupon: `${baseURL}&m=hzz&a=get_mall_coupon`,
  //设置优惠卷
  set_mall_coupon: `${baseURL}&m=hzz&a=set_mall_coupon`,
  //获取商品分类
  get_mall_category_app: `${baseURL}&m=hzz&a=get_mall_category_app`,
  // 发送短信验证码
  getsmscode: `${baseURL}&m=user&a=getsmscode`,
  // 登录
  login: `${baseURL}&m=user&a=login`,
  // 注册
  register: `${baseURL}&m=user&a=register`,
  // 找回密码
  take_password: `${baseURL}&m=user&a=take_password`,
  // 图片验证码
  get_image_captcha: `${baseURL}&m=user&a=get_image_captcha`,
  check_image_captcha: `${baseURL}&m=user&a=check_image_captcha`,
  // 修改基本信息
  set_baseinfo: `${baseURL}&m=user&a=set_baseinfo`,
  // 获取用户资料
  getuserinfo: `${baseURL}&m=user&a=getuserinfo`,
  // 获取上级信息
  getadminbillno: `${baseURL}&m=mall&a=getadminbillno`,
  // 商品进出货处理
  setstock: `${baseURL}&m=main&a=setstock`,
  // 查询库存
  select_hzzbuy_qty: `${baseURL}&m=mall&a=select_hzzbuy_qty`,
  // -- 主应用
  // 地理区域
  geographic: `${baseURL}&m=main&a=geographic`,
  //设置优惠卷上下架
  set_mcoup_staus: `${baseURL}&m=hzz&a=set_mcoup_staus`,
  // 获取客户信息
  getcustomer: `${baseURL}&m=main&a=getcustomer`,
  // 新增、修改客户信息
  setcustomer: `${baseURL}&m=main&a=setcustomer`,
  // 删除客户信息
  delcustomer: `${baseURL}&m=main&a=delcustomer`,
  //获取合同
  get_contract: `${baseURL}&m=main&a=get_contract`,
  // -- 纺织生产
  // 进配管理 查询
  getcrudelist: `${baseURL}&m=cloth&a=getcrudelist`,
  // 进配管理 已检胚布
  getcrudechecklist: `${baseURL}&m=cloth&a=getcrudechecklist`,
  // 进配管理 已检胚布
  getcrudenochecklist: `${baseURL}&m=cloth&a=getcrudenochecklist`,
  // 进配管理 进胚审核
  checkcrude: `${baseURL}&m=cloth&a=checkcrude`,
  // 工艺规格 删除
  delart: `${baseURL}&m=cloth&a=delart`,
  // 工艺规格 新增、编辑
  setart: `${baseURL}&m=cloth&a=setart`,
  // 删除生产原料记录
  delcrude: `${baseURL}&m=cloth&a=delcrude`,
  // 获取客户加工厂信息
  getartlist: `${baseURL}&m=cloth&a=getartlist`,
  // 工作人员 查询
  geworker: `${baseURL}&m=cloth&a=geworker`,
  // 工作人员 新增、编辑
  setworker: `${baseURL}&m=cloth&a=setworker`,
  // 工作人员 删除
  delworker: `${baseURL}&m=cloth&a=delworker`,
  // 添加生产原料
  setcrude: `${baseURL}&m=cloth&a=setcrude`,
  // 车型车牌 查询
  getcarplate: `${baseURL}&m=cloth&a=getcarplate`,
  // 车型车牌 新增、编辑
  setcarplate: `${baseURL}&m=cloth&a=setcarplate`,
  // 车型车牌 删除
  delcarplate: `${baseURL}&m=cloth&a=delcarplate`,
  // 仓库设置 查询
  getdepot: `${baseURL}&m=cloth&a=getdepot`,
  // 仓库设置 新增、编辑
  setdepot: `${baseURL}&m=cloth&a=setdepot`,
  // 仓库设置 删除
  deldepot: `${baseURL}&m=cloth&a=deldepot`,
  // 码单管理 查询
  gettracklist: `${baseURL}&m=cloth&a=gettracklist`,
  // 码单删除
  deltrack: `${baseURL}&m=cloth&a=deltrack`,
  // 码单 新增、编辑
  settrack: `${baseURL}&m=cloth&a=settrack`,
  // 送货管理 录入订单
  setsalorder: `${baseURL}&m=cloth&a=setsalorder`,
  // 送货管理 查询订单头
  getsalorderhead: `${baseURL}&m=cloth&a=getsalorderhead`,
  // 送货管理 查询订单体
  getsalorderbody: `${baseURL}&m=cloth&a=getsalorderbody`,
  // 送货管理 删除订单头
  delsalorderhead: `${baseURL}&m=cloth&a=delsalorderhead`,
  // 送货管理 删除订单体
  delsalorderbody: `${baseURL}&m=cloth&a=delsalorderbody`,
  // 仓库管理 录入订单
  setwaretrackorder: `${baseURL}&m=cloth&a=setwaretrackorder`,
  // 仓库管理 查询订单头
  getwaretrackhead: `${baseURL}&m=cloth&a=getwaretrackhead`,
  // 仓库管理 查询订单体
  getwaretrackbody: `${baseURL}&m=cloth&a=getwaretrackbody`,
  // 仓库管理 删除订单头
  delwaretrackhead: `${baseURL}&m=cloth&a=delwaretrackhead`,
  // 仓库管理 删除订单体
  delwaretrackbody: `${baseURL}&m=cloth&a=delwaretrackbody`,

  // -- 仓存管理
  // 查询进出货统计
  get_stock_statistic: `${baseURL}&m=main&a=get_stock_statistic`,
  // 查询进出货的详细
  get_stock_body: `${baseURL}&m=main&a=get_stock_body`,
  // 商品进出货处理
  getwares: `${baseURL}&m=main&a=getwares`,
  // 商品进出货 删除记录
  delstock: `${baseURL}&m=main&a=delstock`,
  // 商品调仓
  getrollover: `${baseURL}&m=main&a=getrollover`,

  // 退货列表
  get_saleback_list: `${baseURL}&m=main&a=get_saleback_list`,
  // 退货添加数据
  return_wares: `${baseURL}&m=main&a=return_wares`,

  // 盘点列表
  get_stockcheck_list: `${baseURL}&m=main&a=get_stockcheck_list`,
  // 盘点添加, 编辑
  check_wares: `${baseURL}&m=main&a=check_wares`,

  // 查询积分规则
  getpointrulelist: `${baseURL}&m=main&a=getpointrulelist`,
  // 添加、修改积分规则
  setpointrule: `${baseURL}&m=main&a=setpointrule`,
  // 删除积分规则
  delpointrule: `${baseURL}&m=main&a=delpointrule`,
  // 查询积分规则分类
  getpointitemlist: `${baseURL}&m=main&a=getpointitemlist`,
  // 查询我的积分
  getpointtracklist: `${baseURL}&m=main&a=getpointtracklist`,
  // 添加、修改积分规则
  setpointtrack: `${baseURL}&m=main&a=setpointtrack`,
  // 删除积分规则
  delpointtrack: `${baseURL}&m=main&a=delpointtrack`,
  // 取得团队的人员列表
  getteamselectlist: `${baseURL}&m=group&a=getteamselectlist`,
  // 我的团队
  getgroupuserlist: `${baseURL}&m=group&a=getgroupuserlist`,
  // 我的分队
  getgroupuserinfo: `${baseURL}&m=group&a=getgroupuserinfo`,
  // 添加队员
  addgroupuser: `${baseURL}&m=group2&a=addgroupuser`,
  // 报销 最新的
  getreimbur: `${baseURL}&m=main&a=getreimbur`,

  // 考勤 详情
  getattenddetail: `${baseURL}&m=main&a=getattenddetail`,

  // --我的商城
  // 商品列表- 批发商，代理商
  getmallwares: `${baseURL}&m=main&a=getmallwares`,
  // 查询买入订单
  get_orderin_list: `${baseURL}&m=mall&a=get_orderin_list`,
  orderdetail: `${baseURL}&m=mall&a=orderdetail`,
  // 查询卖出订单
  get_orderout_list: `${baseURL}&m=mall&a=get_orderout_list`,
  // 获取商家信息
  getmerchantinfo: `${baseURL}&m=mall&a=getmerchantinfo`,
  // 商品详情
  goodsdetail: `${baseURL}&m=mall&a=goodsdetail`,
  // 商品列表
  getwares: `${baseURL}&m=main&a=getwares`,
  // 商品 新增&修改
  setwares: `${baseURL}&m=main&a=setwares`,
  // 商品 删除
  delwares: `${baseURL}&m=main&a=delwares`,

  // 商品库存统计图表数据
  getstockchart: `${baseURL}&m=main&a=getstockchart`,

  // 获取轮播图信息
  getbannerinfo: `${baseURL}&m=mall&a=getbannerinfo`,
  // 更新广告显示状态
  updateshopadstatus: `${baseURL}&m=mall&a=updateshopadstatus`,

  // 我的商铺
  // 获取折扣商品
  getmalldiscount: `${baseURL}&m=mall&a=getmalldiscount`,
  // 添加 修改优惠商品
  setmallware: `${baseURL}&m=mall&a=setmallware`,
  delmallwares: `${baseURL}&m=mall&a=delmallwares`,

  // 壹软内部
  // 实名审核
  check_realname: `${baseURL}&m=mall&a=check_realname`,
  // 设置实名审核状态
  set_realname: `${baseURL}&m=mall&a=set_realname`,
  // 获取客客户信息
  getintent: `${baseURL}&m=manage&a=getintent`,
  // 新客户挖掘  跟进状态信息设置
  setcalltxt: `${baseURL}&m=manage&a=setcalltxt`,
  // 代得理发票列表
  getinvoicelist: `${baseURL}&m=manage&a=getinvoicelist`,
  // 发票物流寄出
  postinvoice: `${baseURL}&m=manage&a=postinvoice`,
  // 内容审核
  getreviewlist: `${baseURL}&m=manage&a=getreviewlist`,
  // 内容处理审核
  doreview: `${baseURL}&m=manage&a=doreview`,
  // 添加广告
  addshopad: `${baseURL}&m=mall&a=addshopad`,
  // 获取提现列表
  withdraw_list: `${baseURL}&m=main&a=withdraw_list`,
  // 处理提现
  handle_withdraw: `${baseURL}&m=main&a=handle_withdraw`,
  // 取得待审新用户列表
  getwholesalelist: `${baseURL}&m=manage&a=getwholesalelist`,
  // 处理货真真相关新用户审核
  dowholesale: `${baseURL}&m=manage&a=dowholesale`,

  // 分析页
  // 获得销售金额
  getstockmoney: `${baseURL}&m=mall&a=getstockmoney`,
  // 获取客访问量
  getcustomervisits: `${baseURL}&m=mall&a=getcustomervisits`,
};
