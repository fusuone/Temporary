import React, { PureComponent, Fragment } from 'react';
import {
  Divider,
  Dropdown,
  Modal,
  Menu,
  Form,
  Icon,
  Col,
  Row,
  Spin,
  Card,
  Button,
  Table,
  message,
  Input,
} from 'antd';
import * as XLSX from 'xlsx';
import ExportJsonExcel from 'js-export-excel';
import { connect } from 'dva';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import http from '@/utils/http';
import styles from './WareSetting.less';
import CrudeAdd from './CrudeAdd1';

const { Search } = Input;
@connect(({ user }) => ({
  currentUser: user.currentUser,
}))
@Form.create()
class WareSetting extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      getLoading: true,
      info: {}, //商城信息
      stockCheckDate: [],
      RealName_billno: '',
      modalVisible: false, //modal
      status: -10,
      modalData: {},
      updateData: [], //导入的数据
      EditModalVisible: false,
      EditModalData: {},
      //
      previewVisible: false,
      previewImage: '',
      fileList: [],
      fileImage: [],
      img: '',
      onTrack: false,
    };
    this.columns = [
      {
        title: '商品图片',
        width: 130,
        dataIndex: 'image1',
        key: 'image1',
        fixed: 'left',
        render: (text, record) => {
          return <img style={{ width: 100, height: 60 }} src={record.image1} />;
        },
      },
      {
        title: '商品名称',
        width: 150,
        dataIndex: 'waresname',
        key: 'waresname',
      },
      {
        title: '商品编码',
        dataIndex: 'productno',
        key: 'productno',
        width: 150,
      },
      {
        title: '商品类型',
        dataIndex: 'type',
        key: 'type',
        width: 120,
        render: (text, record) => {
          return <div>{record.type == 0 ? '进销的产品' : '生产的产品'}</div>;
        },
      },
      {
        title: '商城在售',
        dataIndex: 'onsale',
        key: 'onsale',
        width: 120,
        render: (text, record) => {
          return <div>{record.type == 0 ? '下架不在售' : '上架在售'}</div>;
        },
      },
      {
        title: '库存数',
        dataIndex: 'qty',
        key: 'qty',
        width: 100,
        render: (text, record) => {
          return (
            <div>
              {record.qty <= 100 ? (
                <div style={{ color: 'red' }}>{record.qty}</div>
              ) : (
                <div>{record.qty}</div>
              )}
            </div>
          );
        },
      },
      {
        title: '次品',
        dataIndex: 'qty1',
        key: 'qty1',
        width: 100,
      },
      {
        title: '坏品',
        dataIndex: 'qty2',
        key: 'qty2',
        width: 100,
      },
      {
        title: '其他',
        dataIndex: 'qty3',
        key: 'qty3',
        width: 100,
      },
      {
        title: '单位',
        dataIndex: 'unit',
        key: 'unit',
        width: 100,
      },
      {
        title: '添加时间',
        dataIndex: 'billdate',
        key: 'billdate',
      },
      {
        title: '操作',
        key: 'action',
        fixed: 'right',
        width: 150,
        render: text => (
          <Fragment>
            <a onClick={ev => this.onTrack(text)}>详情</a>
            <Divider type="vertical" />
            <Dropdown
              overlay={
                <Menu>
                  <Menu.Item>
                    <a onClick={() => this.gotoEdit(text)}>编辑</a>
                    <a onClick={ev => this.delTrack(ev, text)}>删除</a>
                  </Menu.Item>
                </Menu>
              }
            >
              <a>
                更多 <Icon type="down" />
              </a>
            </Dropdown>
          </Fragment>
        ),
      },
    ];
    this.state = {
      listData: {
        list: [],
        total: 0,
      },
      aonTrackItem: {
        image1: '',
      },
      reqParams: {
        admin: this.props.currentUser.admin, //管理员billno
        keyword: '',
        pagesize: 15,
        page: 1,
      },
      getLoading: false, //加载动画
      delLoading: false, //删除中
      showTableAdd: false,
      addOrEdit: null,
      auditFlag: null,
    };
    props.getContext && props.getContext(this);
  }

  componentDidMount() {
    this.getList();
  }
  /////////////////////////////////////////////////////////////////////
  //导入模板
  modelExcel = () => {
    let option = {};
    let dataTable = [];
    let obj = {
      商品名称: '打印机', //名称
      型号: '11111111122', //型号
      商品编码: '123456', //编码
      产品的分类: '121212', //series
      价格: '123456', //
      商城价格: '123456', //
      单位: '箱', //
      商品描述: 'description', //
    };
    dataTable.push(obj);
    option.fileName = '商品导入数据模板';
    option.datas = [
      {
        sheetData: dataTable,
        sheetName: '商品导入数据模板',
        sheetFilter: [
          '商品名称',
          '型号',
          '商品编码',
          '产品的分类',
          '价格',
          '商城价格',
          '单位',
          '商品描述',
        ],
        sheetHeader: [
          '商品名称',
          '型号',
          '商品编码',
          '产品的分类',
          '价格',
          '商城价格',
          '单位',
          '商品描述',
        ],
      },
    ];

    var toExcel = new ExportJsonExcel(option);
    toExcel.saveExcel();
  };

  //导出全部
  downloadAll = () => {
    http({
      method: 'post',
      api: 'getwares',
      params: {
        admin: this.state.reqParams.admin,
        keyword: this.state.reqParams.keyword,
        ispaging: 2,
      },
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          message.info('导出成功');
          this.downloadExcel(data.list);
        } else {
          message.info(msg);
        }
      })
      .catch(() => {
        message.info('导出失败');
      });
  };
  //导出本页
  downloadShowPage = data => {
    this.downloadExcel(data);
  };

  //导出订单数据为excel格式文件
  downloadExcel = data => {
    var option = {};
    let dataTable = [];
    if (data) {
      for (let i in data) {
        if (data) {
          let obj = {
            billno: data[i].billno,
            管理员号码: data[i].管理员号码,
            操作员: data[i].username,
            商品名称: data[i].waresname,
            商品类型: data[i].type == 0 ? '进销产品' : '生产的产品',
            系列: data[i].series,
            型号: data[i].model,
            价格: data[i].price,
            商城中的价格: data[i].mallprice,
            商城在售: data[i].onsale == 1 ? '上架在售' : '下架不在售',
            单位: data[i].unit,
            库存数量: data[i].qty,
            次品数量: data[i].qty1,
            坏品数量: data[i].qty2,
            其它数量: data[i].qty3,
            商品描述: data[i].description,
            商品编码: data[i].productno,
            图片一: data[i].image1,
            图片二: data[i].image2,
            图片三: data[i].image3,
          };
          dataTable.push(obj);
        }
      }
    }
    option.fileName = '商品资料数据';
    option.datas = [
      {
        sheetData: dataTable,
        sheetName: '商品资料数据',
        sheetFilter: [
          'billno',
          '管理员号码',
          '操作员',
          '商品名称',
          '商品类型',
          '系列',
          '型号',
          '价格',
          '商城中的价格',
          '商城在售',
          '单位',
          '库存数量',
          '次品数量',
          '坏品数量',
          '其它数量',
          '商品描述',
          '商品编码',
          '图片一',
          '图片二',
          '图片三',
        ],
        sheetHeader: [
          'billno',
          '管理员号码',
          '操作员',
          '商品名称',
          '商品类型',
          '系列',
          '型号',
          '价格',
          '商城中的价格',
          '商城在售',
          '单位',
          '库存数量',
          '次品数量',
          '坏品数量',
          '其它数量',
          '商品描述',
          '商品编码',
          '图片一',
          '图片二',
          '图片三',
        ],
      },
    ];

    var toExcel = new ExportJsonExcel(option);
    toExcel.saveExcel();
  };

  /////导入操作
  onImportExcel = file => {
    // 获取上传的文件对象
    const { files } = file.target;
    // 通过FileReader对象读取文件
    const fileReader = new FileReader();
    fileReader.onload = event => {
      try {
        const { result } = event.target;
        // 以二进制流方式读取得到整份excel表格对象
        const workbook = XLSX.read(result, { type: 'binary' });
        // 存储获取到的数据
        let data = [];
        // 遍历每张工作表进行读取（这里默认只读取第一张表）
        for (const sheet in workbook.Sheets) {
          // esline-disable-next-line
          if (workbook.Sheets.hasOwnProperty(sheet)) {
            // 利用 sheet_to_json 方法将 excel 转成 json 数据
            data = data.concat(XLSX.utils.sheet_to_json(workbook.Sheets[sheet]));
            // break; // 如果只取第一张表，就取消注释这行
          }
        }
        // 最终获取到并且格式化后的 json 数据
        message.success('上传成功！');
        this.setState({
          updateData: data,
        });
        this.addProduct(data);
      } catch (e) {
        // 这里可以抛出文件类型错误不正确的相关提示
        message.error('文件类型不正确！');
      }
    };
    // 以二进制方式打开文件
    fileReader.readAsBinaryString(files[0]);
  };

  //
  addProduct = data => {
    data.map((v, k) => {
      let reqParams_1 = {
        image1: 'https://wolf.kassor.cn/team/assets/images/20190626160050_5495_200.jpg',
        image2: '',
        image3: '',
        ubillno: '',
        admin: this.props.currentUser.admin,
        username: this.props.currentUser.username,
        waresname: v['商品名称'],
        model: v['型号'],
        productno: v['商品编码'],
        price: v['价格'],
        mallprice: v['商城价格'],
        unit: v['单位'],
        describe: v['商品秒速'],
        series: v['产品分类'],
      };
      http({
        method: 'post',
        api: 'setwares',
        data: reqParams_1,
      })
        .then(result => {
          const { status, msg, data } = result;
          if (status === '0') {
          } else {
            message.warn(msg);
          }
        })
        .catch(() => {});
    });
    this.getList();
  };
  //搜索
  onSearch = value => {
    this.state.reqParams.keyword = value;
    this.getList();
  };
  gotoAdd = () => {
    this.setState({
      showCrudeAdd: true,
      addOrEdit: '0',
      activeItem: {},
    });
  };
  //删除
  delTrack = (ev, text) => {
    Modal.confirm({
      title: `商品信息删除`,
      content: `你确定要把商品billno为 ${text.billno} 的商品执行删除操作吗？`,
      okText: '确认',
      cancelText: '取消',
      onOk: () => this.confirmHandleOk(text.billno),
      onCancel: this.confirmHandleCencle,
    });
  };
  //取消删除
  confirmHandleCencle = () => {
    message.info('操作已取消');
  };
  //删除
  confirmHandleOk = d => {
    http({
      method: 'get',
      api: 'delwares',
      params: {
        items: d,
      },
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          message.info('删除成功');
          this.getList();
        } else {
          message.warn(msg);
        }
      })
      .catch(() => {
        message.info('操作失败');
      });
  };
  // 去编辑
  gotoEdit = item => {
    this.setState({
      addOrEdit: '1',
      activeItem: item,
      showCrudeAdd: true,
    });
    this.getList();
  };
  // 详情
  onTrack = async item => {
    await this.setState({
      aonTrackItem: item,
      onTrack: true,
    });
  };
  handleCancel = () => {
    this.setState({
      onTrack: false,
    });
  };
  //分页
  handleTableChange = (page, pageSize) => {
    this.state.reqParams.page = page;
    this.getList();
  };
  getList = () => {
    const { getLoading, reqParams } = this.state;
    if (getLoading) return;
    this.setState({ getLoading: true });
    http({
      method: 'get',
      api: 'getwares',
      params: {
        ...reqParams,
      },
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            listData: {
              list: data.list,
              total: Number(data.total),
            },
            getLoading: false,
          });
        } else {
          message.warn(msg);
          this.setState({
            listData: {
              list: [1],
            },
            getLoading: false,
          });
        }
      })
      .catch(() => {
        this.setState({ getLoading: false });
      });
  };

  render() {
    const { listData, getLoading, delLoading, stockCheckDate, aonTrackItem } = this.state;
    return (
      <PageHeaderWrapper>
        <div>
          {/* spinning  是否为加载状态 */}
          <Spin spinning={getLoading || delLoading}>
            <Card bordered={false}>
              <Row gutter={24}>
                <Col style={{ height: '80px', width: '100%', textAlign: 'center' }}>
                  <Search
                    placeholder="请输入商品名称或商品编码"
                    onSearch={this.onSearch}
                    enterButton="搜索"
                    style={{ textAlign: 'left', width: '320px' }}
                  />
                </Col>
                <Col style={{ height: '50px', width: '1080px', textAlign: 'center' }}>
                  <Button
                    icon="plus"
                    type="primary"
                    onClick={this.gotoAdd}
                    style={{ marginRight: '25px' }}
                  >
                    新增商品进货信息
                  </Button>
                  <Button type="primary" className={styles['upload-wrap']}>
                    <Icon type="upload" />
                    <input
                      className={styles['file-uploader']}
                      type="file"
                      accept=".xlsx, .xls"
                      onChange={this.onImportExcel}
                    />
                    <span className={styles['upload-text']}>批量导入商品数据</span>
                  </Button>
                  <p className={styles['upload-tip']}>(支持 .xlsx、.xls 格式的文件)</p>
                  <Button onClick={this.modelExcel} style={{ marginLeft: '15px' }}>
                    下载导入模板
                  </Button>
                  <Button
                    onClick={() => this.downloadShowPage(stockCheckDate)}
                    style={{ marginLeft: '15px' }}
                  >
                    导出本页
                  </Button>
                  <Button onClick={this.downloadAll} style={{ marginLeft: '15px' }}>
                    导出全部
                  </Button>
                </Col>
              </Row>
              <Table
                rowKey={record => record.id}
                className={styles['ant-table']}
                scroll={{ x: 1700, y: 600 }} //高
                dataSource={listData.list} //数据来源
                columns={this.columns} //每行显示
                pagination={{
                  current: this.state.reqParams.page,
                  onChange: this.handleTableChange,
                  pageSize: 15,
                  defaultCurrent: 1,
                  total: listData.total,
                }}
              />
            </Card>
          </Spin>
          <CrudeAdd
            visible={this.state.showCrudeAdd} //是否显示添加页面
            addOrEdit={this.state.addOrEdit}
            activeItem={this.state.activeItem}
            handleRefresh={() => this.getList()} //子页面回调刷新列表
            handleVisible={bool => this.setState({ showCrudeAdd: bool })}
          />
        </div>
        <Modal
          visible={this.state.onTrack}
          title="商品详情"
          onCancel={this.handleCancel}
          footer={[
            <Button type="primary" key="back" onClick={this.handleCancel}>
              关闭
            </Button>,
          ]}
        >
          <div className={styles.detailContainer}>
            <p>
              <span>商品图片：</span>
              <span className="content">
                {' '}
                <img
                  style={{ width: '180px', height: '180px' }}
                  src={aonTrackItem.image1}
                  alt="img"
                />
              </span>
            </p>
            <p>
              <span>商品名称：</span>
              <span className="content">{aonTrackItem.waresname}</span>
            </p>
            <p>
              <span>商品billno：</span>
              <span className="content">{aonTrackItem.billno}</span>
            </p>
            <p>
              <span>类型：</span>
              <span className="content">{this.state.type == 0 ? '进销的产品' : '生产的产品'}</span>
            </p>
            <p>
              <span>系列：</span>
              <span className="content">{aonTrackItem.series}</span>
            </p>
            <p>
              <span>型号：</span>
              <span className="content">{aonTrackItem.model}</span>
            </p>
            <p>
              <span>价格：</span>
              <span className="content">{aonTrackItem.price}</span>
            </p>
            <p>
              <span>商城在售：</span>
              <span className="content">
                {aonTrackItem.onsale == 0 ? '下架不在售' : '上架在售'}
              </span>
            </p>
            <p>
              <span>单位：</span>
              <span className="content">{aonTrackItem.unit}</span>
            </p>
            <p>
              <span>库存数量：</span>
              <span className="content">{aonTrackItem.qty}</span>
            </p>
            <p>
              <span>次品：</span>
              <span className="content">{aonTrackItem.qty1}</span>
            </p>
            <p>
              <span>坏品：</span>
              <span className="content">{this.state.aonTrackItem.qty2}</span>
            </p>
            <p>
              <span>其他：</span>
              <span className="content">{aonTrackItem.qty3}</span>
            </p>
            <p>
              <span>产品型号：</span>
              <span className="content">{aonTrackItem.model}</span>
            </p>
            <p>
              <span>产品编码：</span>
              <span className="content">{aonTrackItem.productno}</span>
            </p>
            <p>
              <span>操作员：</span>
              <span className="content">{aonTrackItem.username}</span>
            </p>
            <p>
              <span>添加时间：</span>
              <span className="content">{aonTrackItem.billdate}</span>
            </p>
            <p>
              <span>描述：</span>
              <span className="content">{aonTrackItem.description}</span>
            </p>
          </div>
        </Modal>
      </PageHeaderWrapper>
    );
  }
}
export default WareSetting;
