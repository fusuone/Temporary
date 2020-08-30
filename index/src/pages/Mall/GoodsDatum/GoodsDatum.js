import React, { PureComponent } from 'react';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import {
  Table,
  Card,
  Divider,
  Menu,
  Dropdown,
  Icon,
  Spin,
  Button,
  Modal,
  Select,
  message,
  Input,
  Row,
  Col,
  Form,
  Radio,
  Upload,
} from 'antd';
import { connect } from 'dva';
import http from '@/utils/http';
import * as XLSX from 'xlsx';
import Api from '@/common/api';
import ExportJsonExcel from 'js-export-excel';
import styles from './GoodsDatum.less';
const { Search } = Input;

function getBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
}

@connect(({ user }) => ({
  currentUser: user.currentUser,
}))
@Form.create()
class GoodsDatum extends PureComponent {
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
      reqParams: {
        admin: '', //管理员billno
        keyword: '',
        pagesize: 15,
        page: 1,
      },
      updateData: [], //导入的数据
      total: 0,
      EditModalVisible: false,
      EditModalData: {},
      //
      previewVisible: false,
      previewImage: '',
      fileList: [],
      fileImage: [],
      img: '',
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
      },
      {
        title: '次品',
        dataIndex: 'qty1',
        key: 'uniqty1t',
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
        key: 'operation',
        fixed: 'right',
        width: 130,
        render: (text, record) => {
          let OperateMenu = (
            <Menu>
              <Menu.Item>
                <a className={styles.operrateColor} onClick={() => this.EidtShowModal(record)}>
                  编辑
                </a>
                <a className={styles.operrateColor} onClick={ev => this.confirm(ev, record)}>
                  删除
                </a>
              </Menu.Item>
            </Menu>
          );
          return (
            <div>
              <a onClick={this.showModal}>详情</a>
              <Divider type="vertical" />
              <Dropdown overlay={OperateMenu}>
                <span style={{ color: '#1890ff' }} className="ant-dropdown-link">
                  更多 <Icon type="down" />
                </span>
              </Dropdown>
            </div>
          );
        },
      },
    ];
  }

  componentDidMount() {
    this.getAdminInfo();
  }

  //提示
  confirm = (ev, record) => {
    Modal.confirm({
      title: `商品信息删除`,
      content: `你确定要把商品billno为 ${record.billno} 的商品执行删除操作吗？`,
      okText: '确认',
      cancelText: '取消',
      onOk: () => this.confirmHandleOk(record.billno),
      onCancel: this.confirmHandleCencle,
    });
  };

  confirmHandleCencle = () => {
    message.info('操作已取消');
  };

  deletePage = d => {
    let ad = this.state.stockCheckDate;
    ad.map((v, k) => {
      this.confirmHandleOk(v.billno);
    });
  };

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

  //获取管理员信息
  getAdminInfo = () => {
    http({
      method: 'get',
      api: 'getadminbillno',
      params: {
        admin: this.props.currentUser.admin,
      },
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.state.reqParams.admin = data[0].userno;
          this.getList();
        } else {
          message.warn(msg);
        }
      })
      .catch(() => {
        // this.setState({ getLoading: false });
      });
  };

  //设置编辑区域的值
  getModalData = key => {
    let key1 = key - 1;
    let modalData1 = this.state.stockCheckDate[key1];
    this.setState({
      modalData: modalData1,
    });
  };

  //获取退货列表数据
  getList = () => {
    const { reqParams } = this.state;
    const params = reqParams;
    http({
      method: 'post',
      api: 'getwares',
      params: reqParams,
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            info: data.info,
            stockCheckDate: data.list,
            total: data.total,
            getLoading: false,
          });
        } else {
          message.warn(msg);
          this.setState({
            info: {},
            stockCheckDate: [],
            getLoading: false,
          });
        }
      })
      .catch(() => {
        this.setState({ getLoading: false });
      });
  };

  //编辑盘点信息
  gotoEdit = text => {
    this.setState({
      EditData: {
        data: text,
        EditModalVisible: true,
      },
    });
  };

  //modal
  showModal = () => {
    this.setState({
      modalVisible: true,
    });
  };

  handleOk = e => {
    this.setState({
      status: 2,
    }),
      this.setRealNameList();
  };

  handleCancel = e => {
    this.setState({
      modalVisible: false,
    });
  };
  //未通过
  notAdopted = e => {
    this.setState({
      status: -1,
    }),
      this.setRealNameList();
  };
  //////////////

  ///未通过原因

  onChange = value => {};

  onBlur = () => {};

  onFocus = () => {};

  onSearch = val => {};
  ///

  //更新审核列表数据
  setRealNameList = () => {
    if (this.state.status == -10) {
      return;
    }
    http({
      method: 'post',
      api: 'set_realname',
      params: {
        userno: this.props.currentUser.userno,
        billno: this.state.modalData['billno'],
        status: this.state.status,
      },
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            getLoading: false,
            modalVisible: false,
            status: -10,
          }),
            this.getList();
        } else {
          message.warn(msg);
          this.setState({
            getLoading: false,
            status: -10,
          });
        }
      })
      .catch(() => {
        this.setState({ getLoading: false, status: -10 });
      });
  };

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

  //分页
  getPaginationdata = (page, pageSize) => {
    this.state.reqParams.page = page;
    this.getList();
  };

  //搜索
  onSearch = value => {
    this.state.reqParams.keyword = value;
    this.getList();
  };

  //////////////////////////////////////////////////////////////////////

  EidtShowModal = data => {
    let { fileList } = this.state;
    for (let i = 1; i < 6; i++) {
      let a = '';
      switch (i) {
        case 1:
          a = data.image1;
          break;
        case 2:
          a = data.image2;
          break;
        case 3:
          a = data.image3;
          break;
        case 4:
          a = data.image4;
          break;
        case 5:
          a = data.image5;
          break;
        case 6:
          a = data.image6;
          break;
      }
      let b = `image${i}`;
      if (!data[b] == '') {
        this.state.fileList[i - 1] = {
          uid: i,
          name: `image${i}`,
          status: 'done',
          url: a,
        };
        this.state.fileImage[i - 1] = data[b];
      }
    }
    this.setState({
      EditModalVisible: true, //编辑模块是否可见
      EditModalData: data,
    });
  };

  EditHandleOk = () => {
    this.setState({ loading: true });
    this.EditHandleSubmit();
  };

  EditHandleCancel = () => {
    this.props.form.resetFields();
    this.setState({
      EditModalVisible: false,
      EditModalData: {},
      fileList: [],
      fileImage: [],
    });
  };

  EditHandleSubmit = e => {
    const { fileImage, EditModalData } = this.state;
    e.preventDefault();
    this.props.form.validateFields((err, values) => {
      if (!err) {
        if (fileImage.length == 0) {
          message.info('请点击添加图片');
          return;
        }
        const data = {
          image1: fileImage[0] ? fileImage[0] : '',
          image2: fileImage[1] ? fileImage[1] : '',
          image3: fileImage[2] ? fileImage[2] : '',
          image4: fileImage[3] ? fileImage[3] : '',
          image5: fileImage[4] ? fileImage[4] : '',
          image6: fileImage[5] ? fileImage[5] : '',
          admin: EditModalData.admin,
          username: this.props.currentUser.username,
          waresname: values.waresname, // 名称
          model: values.model, // 型号
          productno: values.productno, // 编码
          price: values.price, // 单价
          mallprice: values.mallprice, // 商城单价
          unit: values.unit, // 单位
          describe: values.describe, // 描述
          billno: EditModalData.billno, // 有值则修改
          checkedIndex: values.checkedIndex,
          series: values.series, // 产品的分类
        };
        http({
          method: 'post',
          api: 'setwares',
          data: {
            ...data,
          },
        })
          .then(result => {
            const { status, msg, data } = result;
            if (status === '0') {
              message.info('数据修改成功');
              this.EditHandleCancel();
              this.getList();
            } else {
              message.info(msg);
            }
          })
          .catch(() => {
            message.info('修改失败');
          });
      }
    });
  };

  /////////////////////////////////////////////
  handleCancelPreview = async file => {
    this.setState({ previewVisible: false });
  };

  handlePreview = async file => {
    if (!file.url && !file.preview) {
      file.preview = await getBase64(file.originFileObj);
    }

    this.setState({
      previewImage: file.url || file.preview,
      previewVisible: true,
    });
  };

  handleChangePreview = ({ fileList }) => {
    const fileImageArray = [];
    for (let i = 0; i < fileList.length; i++) {
      let a = '';
      if (fileList[i].response) {
        a = fileList[i].response.data.source;
      } else {
        a = fileList[i].url;
      }
      fileImageArray[i] = a;
    }
    setTimeout(() => {
      this.setState({
        fileImage: fileImageArray,
      });
    }, 4000);
    this.setState({
      fileList,
    });
  };

  beforeUpload = (file, fileList) => {};
  render() {
    const {
      getLoading,
      modalData,
      stockCheckDate,
      EditModalVisible,
      EditModalData,
      fileList,
      previewVisible,
      previewImage,
    } = this.state;
    const { Option } = Select;
    const { getFieldDecorator } = this.props.form;
    const uploadButton = (
      <div>
        <Icon type="plus" />
        <div className="ant-upload-text">添加</div>
      </div>
    );

    return (
      <PageHeaderWrapper>
        <div className={styles.example}>
          <Spin spinning={getLoading} />
        </div>
        <Card>
          <Row gutter={24}>
            <Col style={{ height: '80px', width: '100%', textAlign: 'center' }}>
              <Search
                placeholder="请输入商品名称或商品编码"
                onSearch={this.onSearch}
                enterButton="搜索"
                size="large"
                className={styles.search1}
                style={{ width: '320px' }}
              />
            </Col>
            <Col style={{ height: '50px', textAlign: 'center' }}>
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
            className={styles['ant-table']}
            //点击显示详情
            rowKey={record => record.id}
            onRow={record => {
              return {
                onClick: event => {
                  this.setState({
                    modalData: record,
                    RealName_billno: record.billno,
                  });
                }, // 点击行
              };
            }}
            columns={this.columns}
            dataSource={this.state.stockCheckDate}
            scroll={{ x: 1500, y: 600 }}
            pagination={{
              current: this.state.reqParams.page,
              onChange: this.getPaginationdata,
              pageSize: 15,
              defaultCurrent: 1,
              total: this.state.total,
            }}
          />
        </Card>
        {/* modal */}
        <div>
          <Modal
            title="盘点详情"
            visible={this.state.modalVisible}
            onOk={this.handleOk}
            onCancel={this.handleCancel}
            maskClosable={false}
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
                    src={this.state.modalData.image1}
                    alt="img"
                  />
                </span>
              </p>
              <p>
                <span>商品名称：</span>
                <span className="content">{this.state.modalData.waresname}</span>
              </p>
              <p>
                <span>商品billno：</span>
                <span className="content">{this.state.modalData.billno}</span>
              </p>
              <p>
                <span>类型：</span>
                <span className="content">
                  {this.state.modalData.type == 0 ? '进销的产品' : '生产的产品'}
                </span>
              </p>
              <p>
                <span>系列：</span>
                <span className="content">{this.state.modalData.series}</span>
              </p>
              <p>
                <span>型号：</span>
                <span className="content">{this.state.modalData.model}</span>
              </p>
              <p>
                <span>价格：</span>
                <span className="content">{this.state.modalData.price}</span>
              </p>
              <p>
                <span>商城在售：</span>
                <span className="content">
                  {this.state.modalData.onsale == 0 ? '下架不在售' : '上架在售'}
                </span>
              </p>
              <p>
                <span>单位：</span>
                <span className="content">{this.state.modalData.unit}</span>
              </p>
              <p>
                <span>库存数量：</span>
                <span className="content">{this.state.modalData.qty}</span>
              </p>
              <p>
                <span>次品：</span>
                <span className="content">{this.state.modalData.qty1}</span>
              </p>
              <p>
                <span>坏品：</span>
                <span className="content">{this.state.modalData.qty2}</span>
              </p>
              <p>
                <span>其他：</span>
                <span className="content">{this.state.modalData.qty3}</span>
              </p>
              <p>
                <span>产品型号：</span>
                <span className="content">{this.state.modalData.model}</span>
              </p>
              <p>
                <span>产品编码：</span>
                <span className="content">{this.state.modalData.productno}</span>
              </p>
              <p>
                <span>操作员：</span>
                <span className="content">{this.state.modalData.username}</span>
              </p>
              <p>
                <span>添加时间：</span>
                <span className="content">{this.state.modalData.billdate}</span>
              </p>
              <p>
                <span>描述：</span>
                <span className="content">{this.state.modalData.description}</span>
              </p>
            </div>
          </Modal>
        </div>
        {/* /////////////////////////////////// */}
        <Modal
          visible={EditModalVisible}
          maskClosable={false}
          title="编辑"
          onOk={this.EditHandleOk}
          onCancel={this.EditHandleCancel}
          footer={[
            <Button key="back" onClick={this.EditHandleCancel}>
              取消
            </Button>,
            <Button key="submit" type="primary" onClick={this.EditHandleSubmit}>
              确认
            </Button>,
          ]}
        >
          <Form layout="vertical">
            <Row gutter={24}>
              <Col>
                <Form.Item label="图片：">
                  <Upload
                    name="file"
                    action={Api.uploadimg}
                    listType="picture-card"
                    fileList={fileList}
                    onPreview={this.handlePreview}
                    onChange={this.handleChangePreview}
                  >
                    {fileList.length >= 3 ? null : uploadButton}
                  </Upload>
                  <Modal visible={previewVisible} footer={null} onCancel={this.handleCancelPreview}>
                    <img
                      className={styles.previewImage}
                      alt="example"
                      style={{ width: '100%' }}
                      src={previewImage}
                    />
                  </Modal>
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="商品名称：">
                  {getFieldDecorator('waresname', {
                    rules: [{ required: true, message: '请输入商品名称' }],
                    initialValue: EditModalData.waresname,
                  })(<Input placeholder="请输入商品名称" />)}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="商品型号：">
                  {getFieldDecorator('model', {
                    rules: [{ required: true, message: '请输入商品型号' }],
                    initialValue: EditModalData.model,
                  })(<Input placeholder="请输入商品型号" />)}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="商品编码：">
                  {getFieldDecorator('productno', {
                    rules: [{ required: true, message: '请输入商品编码' }],
                    initialValue: EditModalData.productno,
                  })(<Input placeholder="请输入商品编码" />)}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="价格：">
                  {getFieldDecorator('price', {
                    rules: [{ required: true, message: '请输入价格' }],
                    initialValue: EditModalData.price,
                  })(<Input placeholder="请输入价格" />)}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="商城单价：">
                  {getFieldDecorator('mallprice', {
                    rules: [{ required: true, message: '请输入商城单价' }],
                    initialValue: EditModalData.mallprice,
                  })(<Input placeholder="请输入商城单价" />)}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="单位：">
                  {getFieldDecorator('unit', {
                    rules: [{ required: true, message: '请输入单位' }],
                    initialValue: EditModalData.unit,
                  })(<Input placeholder="请输入单位" />)}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="商品商品系列：">
                  {getFieldDecorator('series', {
                    rules: [{ required: true, message: '请输入商品系列' }],
                    initialValue: EditModalData.series,
                  })(<Input placeholder="请输入商品系列" />)}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="商城在售">
                  {getFieldDecorator('checkedIndex', {
                    rules: [{ required: true, message: '请选择商城在售' }],
                  })(
                    <Radio.Group>
                      <Radio value={0}>下架不在兽</Radio>
                      <Radio value={1}>上架在售</Radio>
                    </Radio.Group>
                  )}
                </Form.Item>
              </Col>

              <Col>
                <Form.Item label="备注：">
                  {getFieldDecorator('describe', {
                    rules: [{ required: true, message: '请输入备注信息' }],
                    initialValue: EditModalData.describe,
                  })(
                    <textarea
                      style={{ width: '100%', height: '100px' }}
                      placeholder="请输入备注信息"
                    />
                  )}
                </Form.Item>
              </Col>
            </Row>
          </Form>
        </Modal>
      </PageHeaderWrapper>
    );
  }
}
export default GoodsDatum;
