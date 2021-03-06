export default [
  // user
  {
    path: '/user',
    component: '../layouts/UserLayout',
    routes: [
      { path: '/user', redirect: '/user/login' },
      { path: '/user/login', component: './User/Login' },
      { path: '/user/register', component: './User/Register' },
      { path: '/user/register-result', component: './User/RegisterResult' },
      { path: '/user/take-password', component: './User/TakePassword' },
      { path: '/user/take-password-result', component: './User/TakePasswordResult' }
    ]
  },
  // app
  {
    path: '/',
    component: '../layouts/BasicLayout',
    Routes: ['src/pages/Authorized'],
    routes: [
      // dashboard
      { path: '/', redirect: '/dashboard/analysis' },
      {
        path: '/dashboard',
        name: 'dashboard',
        icon: 'dashboard',
        routes: [
          {
            path: '/dashboard/analysis',
            name: 'analysis',
            component: './Dashboard/Analysis'
          },
          {
            path: '/dashboard/monitor',
            name: 'monitor',
            component: './Dashboard/Monitor'
          },
          {
            path: '/dashboard/workplace',
            name: 'workplace',
            component: './Dashboard/Workplace'
          },
          {
            path: '/dashboard/statistics',
            name: 'statistics',
            component: './Dashboard/Statistics'
          }
        ]
      },
      // 纺织生产
      {
        path: '/textile',
        name: 'textile',
        icon: 'disconnect',
        authority: 'admin',
        routes: [
          { // 进胚管理
            path: '/textile/crude',
            name: 'crude',
            component: './Textile/Crude',
            hideChildrenInMenu: true,
            routes: [
              {
                path: '/textile/crude',
                redirect: '/textile/crude/crudein'
              },
              {
                path: '/textile/crude/crudein',
                name: 'crudein',
                component: './Textile/Crude/CrudeIn/CrudeIn'
              },
              {
                path: '/textile/crude/check-list',
                name: 'check-list',
                component: './Textile/Crude/CheckList/CheckList'
              }
            ]
          },
          { // 码单管理
            path: '/textile/track',
            name: 'track',
            component: './Textile/Track',
            hideChildrenInMenu: true,
            routes: [
              {
                path: '/textile/track',
                redirect: '/textile/track/cut'
              },
              {
                path: '/textile/track/cut',
                name: 'cut',
                component: './Textile/Track/TrackCut/TrackCut'
              }, {
                path: '/textile/track/list',
                name: 'list',
                component: './Textile/Track/TrackList/TrackList'
              }
            ]
          },
          { // 送货管理
            path: '/textile/sale',
            name: 'sale',
            component: './Textile/Sale',
            hideChildrenInMenu: true,
            routes: [
              {
                path: '/textile/sale',
                redirect: '/textile/sale/output'
              },
              {
                path: '/textile/sale/output',
                name: 'output',
                component: './Textile/Sale/Output/Output'
              },
              {
                path: '/textile/sale/order-list',
                name: 'order-list',
                component: './Textile/Sale/OrderList/OrderList'
              }
            ]
          },
          // { // 仓库管理
          //   path: '/textile/warehouse',
          //   name: 'warehouse',
          //   component: './Textile/WareHouse',
          //   hideChildrenInMenu: true,
          //   routes: [
          //     {
          //       path: '/textile/warehouse',
          //       redirect: '/textile/warehouse/ware-in'
          //     },
          //     {
          //       path: '/textile/warehouse/ware-in',
          //       name: 'ware-in',
          //       component: './Textile/WareHouse/WareIn/WareIn'
          //     },
          //     {
          //       path: '/textile/warehouse/ware-list',
          //       name: 'ware-list',
          //       component: './Textile/WareHouse/WareList/WareList'
          //     }
          //   ]
          // },
          { // 基本资料
            path: '/textile/baseinfo',
            name: 'baseinfo',
            component: './Textile/BaseInfo',
            hideChildrenInMenu: true,
            routes: [
              {
                path: '/textile/baseinfo',
                redirect: '/textile/baseinfo/customer'
              },
              {
                path: '/textile/baseinfo/customer',
                name: 'customer',
                component: './Textile/BaseInfo/Customer/Customer'
              },
              {
                path: '/textile/baseinfo/art',
                name: 'art',
                component: './Textile/BaseInfo/Art/Art'
              },
              {
                path: '/textile/baseinfo/car',
                name: 'car',
                component: './Textile/BaseInfo/Car/Car'
              },
              {
                path: '/textile/baseinfo/depot',
                name: 'depot',
                component: './Textile/BaseInfo/Depot/Depot'
              },
              {
                path: '/textile/baseinfo/worker',
                name: 'worker',
                component: './Textile/BaseInfo/Worker/Worker'
              }
            ]
          }
        ]
      },
      { // 仓存管理
        path: '/storehouse',
        name: 'storehouse',
        icon: 'appstore',
        routes: [
          {
            path: '/storehouse',
            redirect: '/storehouse/stock-in'
          },
          {
            path: '/storehouse/stock-in',
            name: 'stock-in',
            component: './StoreHouse/StockIn/StockIn'
          },
          {
            path: '/storehouse/stock-out',
            name: 'stock-out',
            component: './StoreHouse/StockOut/StockOut'
          },
          {
            path: '/storehouse/stock-return',
            name: 'stock-return',
            component: './StoreHouse/StockReturn/StockReturn'
          },
          {
            path: '/storehouse/ware-check',
            name: 'ware-check',
            component: './StoreHouse/StockCheck/StockCheck'
          },
          {
            path: '/storehouse/ware-setting',
            name: 'ware-setting',
            component: './StoreHouse/WareSetting/WareSetting'
          },
          {
            path: '/storehouse/WarehouseGood',
            name: 'WarehouseGood',
            component: './StoreHouse/WarehouseGood/WarehouseGood'
          }
        ]
      },
      // { // 我的商城
      //   path: '/shop',
      //   name: 'shop',
      //   icon: 'shop',
      //   routes: [
      //     {// 订单管理
      //       path: '/shop/preferentialactivity',
      //       name: 'preferentialactivity',
      //       component: './Shop/PreferentialActivity/PreferentialActivity'
      //     }
      //   ]
      // },
      { // 商城交易
        path: '/mall',
        name: 'mall',
        icon: 'shop',
        routes: [
          {// 订单管理
            path: '/mall/bill',
            name: 'bill',
            component: './Mall/Bill/Bill',
            hideChildrenInMenu: true,
            routes: [
              {
                path: '/mall/bill',
                redirect: '/mall/bill/all-orders'
              },
              {
                path: '/mall/bill/all-orders',
                name: 'all-orders',
                component: './Mall/Bill/AllOrders/AllOrders'
              },
              {
                path: '/mall/bill/awaiting-payment',
                name: 'awaiting-payment',
                component: './Mall/Bill/AwaitingPayment/AwaitingPayment'
              },
              {
                path: '/mall/bill/awaiting-shipment',
                name: 'awaiting-shipment',
                component: './Mall/Bill/AwaitingShipment/AwaitingShipment'
              },
              {
                path: '/mall/bill/valuat',
                name: 'valuat',
                component: './Mall/Bill/Valuat/Valuat'
              },
              {
                path: '/mall/bill/regular-purchase',
                name: 'regular-purchase',
                component: './Mall/Bill/RegularPurchase/RegularPurchase'
              },
              {
                path: '/mall/bill/awaiting-send',
                name: 'awaiting-send',
                component: './Mall/Bill/AwaitingSend/AwaitingSend'
              },
              {
                path: '/mall/bill/recycling-bin',
                name: 'recycling-bin',
                component: './Mall/Bill/RecyclingBin/RecyclingBin'
              }
            ]
          },
          {// 卖出订单
            path: '/mall/sell-bill',
            name: 'sell-bill',
            component: './Mall/SellBill/SellBill',
            hideChildrenInMenu: true,
            routes: [
              {
                path: '/mall/sell-bill',
                redirect: '/mall/sell-bill/all-orders'
              },
              {
                path: '/mall/sell-bill/all-orders',
                name: 'all-orders',
                component: './Mall/SellBill/AllOrders/AllOrders'
              },
              {
                path: '/mall/sell-bill/awaiting-payment',
                name: 'awaiting-payment',
                component: './Mall/SellBill/AwaitingPayment/AwaitingPayment'
              },
              {
                path: '/mall/sell-bill/awaiting-shipment',
                name: 'awaiting-shipment',
                component: './Mall/SellBill/AwaitingShipment/AwaitingShipment'
              },
              {
                path: '/mall/sell-bill/valuat',
                name: 'valuat',
                component: './Mall/SellBill/Valuat/Valuat'
              },
              {
                path: '/mall/sell-bill/regular-purchase',
                name: 'regular-purchase',
                component: './Mall/SellBill/RegularPurchase/RegularPurchase'
              },
              {
                path: '/mall/sell-bill/awaiting-send',
                name: 'awaiting-send',
                component: './Mall/SellBill/AwaitingSend/AwaitingSend'
              },
              {
                path: '/mall/sell-bill/recycling-bin',
                name: 'recycling-bin',
                component: './Mall/SellBill/RecyclingBin/RecyclingBin'
              }
            ]
          },
          // {// 商品资料
          //   path: '/mall/goods-datum',
          //   name: 'goods-datum',
          //   component: './Mall/GoodsDatum/GoodsDatum'
          // },
           {// 优惠活动
            path: '/mall/preferentialactivity',
            name: 'preferentialactivity',
            component: './Mall/PreferentialActivity/PreferentialActivity'
          },
          // {//商城装修
          //   path: '/mall/decoration',
          //   name: 'decoration',
          //   authority: 'admin',
          //   component: './Mall/Decoration/Decoration'
          // },   
          
          // {//商品管理
          //   path: '/mall/goods',
          //   name: 'goods',
          //   component: './Mall/Goods/Goods',


          //   // path: '/mall/bill',
          //   // name: 'bill',
          //   // component: './Mall/Bill/Bill',
          //   hideChildrenInMenu: true,
          //   routes: [
          //     {
          //       path: '/mall/goods',
          //       redirect: '/mall/goods/classification-management'
          //     },
          //     {
          //       path: '/mall/goods/classification-management',
          //       name: 'classification-management',
          //       component: './Mall/Goods/ClassificationManagement/ClassificationManagement'
          //     },
          //     {
          //       path: '/mall/goods/brand-management',
          //       name: 'brand-management',
          //       component: './Mall/Goods/BrandManagement/BrandManagement'
          //     },
          //     {
          //       path: '/mall/goods/list-management',
          //       name: 'list-management',
          //       component: './Mall/Goods/ListManagement/ListManagement'
          //     },
          //     {
          //       path: '/mall/goods/repertory-management',
          //       name: 'repertory-management',
          //       component: './Mall/Goods/RepertoryManagement/RepertoryManagement'
          //     },
          //     {
          //       path: '/mall/goods/freight-management',
          //       name: 'freight-management',
          //       component: './Mall/Goods/FreightManagement/FreightManagement'
          //     },
              
          //   ]
          // },
          // {
          //   path: '/mall/release',
          //   name: 'release',
          //   component: './Mall/Release/Release'
          // }
        ]
      },

      { // 团队管理
        path: '/team',
        name: 'team',
        icon: 'team',
        routes: [
          {
            path: '/team',
            redirect: '/team/group'
          },
          {
            path: '/team/group',
            name: 'group',
            component: './Team/Group/Group/Group'
          },
          {
            path: '/team/plan',
            name: 'plan',
            component: './Team/Plan/Plan'
          },
          {
            path: '/team/task',
            name: 'task',
            component: './Team/Task/Task'
          },
          {
            path: '/team/rule',
            name: 'rule',
            component: './Team/Rule',
            hideChildrenInMenu: true,
            routes: [
              {
                path: '/team/rule',
                redirect: '/team/rule/rulein'
              },
              {
                path: '/team/rule/rulein',
                name: 'rulein',
                component: './Team/Rule/Rule/Rule'
              },
              {
                path: '/team/rule/manage',
                name: 'manage',
                component: './Team/Rule/Manage/Manage'
              }
            ]
          },
          // {
          //   path: '/team/expenseAccount',
          //   name: 'expenseAccount',
          //   component: './Team/ExpenseAccount/ExpenseAccount'
          // },

          {
            path: '/team/expenseaccount',
            name: 'expenseaccount',
            component: './Team/ExpenseAccount/ExpenseAccount',
            hideChildrenInMenu: true,
            routes: [
              {
                path: '/team/expenseaccount',
                redirect: '/team/expenseaccount/myself-submit'
              },
              {
              path: '/team/expenseaccount/myself-submit',
              name: 'myself-submit',
              component: './Team/ExpenseAccount/MyselfSubmit/MyselfSubmit'
              },
              {
                path: '/team/expenseaccount/myself-examine',
                name: 'myself-examine',
                component: './Team/expenseaccount/MyselfExamine/MyselfExamine'
              },
            ]
          },
          {
            path: '/team/Record',
            name: 'Record',
            component: './Team/Record/Record'
          }
        ]
      },
      { // 异常页
        name: 'exception',
        icon: 'warning',
        path: '/exception',
        hideInMenu: true,
        routes: [
          {
            path: '/exception/403',
            name: 'not-permission',
            component: './Exception/403'
          },
          {
            path: '/exception/404',
            name: 'not-find',
            component: './Exception/404'
          },
          {
            path: '/exception/500',
            name: 'server-error',
            component: './Exception/500'
          },
          {
            path: '/exception/trigger',
            name: 'trigger',
            hideInMenu: true,
            component: './Exception/TriggerException'
          }
        ]
      },
      

      { // 个人页
        name: 'account',
        icon: 'user',
        path: '/account',
        routes: [
          {
            path: '/account/settings',
            name: 'settings',
            component: './Account/Settings/Info',
            routes: [
              {
                path: '/account/settings',
                redirect: '/account/settings/base'
              },
              {
                path: '/account/settings/base',
                component: './Account/Settings/BaseView'
              },
              {
                path: '/account/settings/security',
                component: './Account/Settings/SecurityView'
              },
              {
                path: '/account/settings/binding',
                component: './Account/Settings/BindingView'
              },
              {
                path: '/account/settings/notification',
                component: './Account/Settings/NotificationView'
              }
            ]
          },
          {
            path: '/account/setout',
            name: 'setout',
            component: './Account/SetOut/SetOut'
          },
          {
            path: '/account/Record',
            name: 'record',
            component: './Account/Record/Record'
          }
        ]
      },
      {
        path:'/hzz',
        name:'hzz',
        icon:'shopping',
        routes: [
          {
            path: '/hzz/MakingCoupons',
            name: 'MakingCoupons',
            component: './hzz/MakingCoupons/MakingCoupons'
          },
          {
            path: '/hzz/CommodityShelves',
            name: 'CommodityShelves',
            component: './hzz/CommodityShelves/CommodityShelves'
          },
          {
            path: '/hzz/PrioritySnapping',
            name: 'PrioritySnapping',
            component: './hzz/PrioritySnapping/PrioritySnapping'
          },
          {
            path: '/hzz/sortSwiper',
            name: 'sortSwiper',
            component: './hzz/sortSwiper/sortSwiper'
          },
          {
            path: '/hzz/FullDiscount',
            name: 'FullDiscount',
            component: './hzz/FullDiscount/FullDiscount'
          },
          {
            path: '/hzz/AfterService',
            name: 'AfterService',
            component: './hzz/AfterService/AfterService'
          },
        ]
      },

       //商城装饰
       {
        path: '/decoration',
        name: 'decoration',
        icon: 'crown',
        authority: 'admin',
        routes: [
          {
            path: '/decoration/preferentialactivity',
            name: 'preferentialactivity',
            component: './Decoration/PreferentialActivity/PreferentialActivity'
          },
          {
            path: '/decoration/indexSwiper',
            name: 'indexSwiper',
            component: './decoration/indexSwiper/indexSwiper'
          },
          {
            path: '/decoration/homepagepicture',
            name: 'homepagepicture',
            component: './Decoration/HomePagePicture/HomePagePicture'
          },
        ]
      },


       //壹软内部       ---- 只对限定的人员开放  
       {
        path: '/interior',
        name: 'interior',
        icon: 'check',
        authority: 'admin',
        routes: [
          {
            path: '/interior/realname',
            name: 'realname',
            component: './Interior/RealName/RealName'
          },

          {// 提现审核
            path: '/interior/withdrawcash',
            name: 'withdrawcash',
            component: './Interior/WithdrawCash/WithdrawCash',
            hideChildrenInMenu: true,
            routes: [
              {
                path: '/interior/withdrawcash',
                component: './Interior/WithdrawCash/All/All'
              },
              {
                path: '/interior/withdrawcash/all',
                name: 'all',
                component: './Interior/WithdrawCash/All/All'
              },
              {
                path: '/interior/withdrawcash/awaiting-examine',
                name: 'awaiting-examine',
                component: './Interior/WithdrawCash/AwaitingExamine/AwaitingExamine'
              },
              {
                path: '/interior/withdrawcash/audit-refusal',
                name: 'audit-refusal',
                component: './Interior/WithdrawCash/AuditRefusal/AuditRefusal'
              },
              {
                path: '/interior/withdrawcash/awaiting-account',
                name: 'awaiting-account',
                component: './Interior/WithdrawCash/AwaitingAccount/AwaitingAccount'
              },
              {
                path: '/interior/withdrawcash/arrived-account',
                name: 'arrived-account',
                component: './Interior/WithdrawCash/ArrivedAccount/ArrivedAccount'
              },
              {
                path: '/interior/withdrawcash/failure-pay',
                name: 'failure-pay',
                component: './Interior/WithdrawCash/FailurePay/FailurePay'
              },
              {
                path: '/interior/withdrawcash/bank-refund',
                name: 'bank-refund',
                component: './Interior/WithdrawCash/BankRefund/BankRefund'
              },      
            ]
          },
          {
            path: '/interior/content',
            name: 'content',
            component: './Interior/Content/Content'
          },
          {
            path: '/interior/receipt',
            name: 'receipt',
            component: './Interior/Receipt/Receipt'
          },
          {
            path: '/interior/customermining',
            name: 'customermining',
            component: './Interior/CustomerMining/CustomerMining'
          },
          {
            path: '/interior/wholesale',
            name: 'wholesale',
            component: './Interior/Wholesale/Wholesale'
          },
        ]
      },                                                                                                                                           
      {
        component: '404'
      }
    ]
  }
];
