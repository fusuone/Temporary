import React, { PureComponent } from 'react';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { Table,Modal,Select,message, DatePicker,InputNumber,Icon,Col, Form,Button ,Row ,Input,Upload} from 'antd';
import http from '@/utils/http';
import Api from '@/common/api';
import moment from 'moment';
import { connect } from 'dva';
@connect(({ user }) => ({
  currentUser: user.currentUser,
}))
@Form.create()
class AfterService extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      getLoading: true,
      moudelVisible:false,
      moudelDate: [],
      detailsVisible:false,
      billno:'',
    };
    this.columns = [
      {
        title: '商品图片',
        width: 130,
        dataIndex: 'image1',
        key: 'image1',
        render: (text, record) => {
          return <img style={{ width: 100, height: 60 }} src={record.image1} />;
        },
      },
      {
        title: '编号',
        dataIndex: 'billno',
        key: 'billno',
        width: 150,
      },
      {
        title: '申请人',
        dataIndex: 'username',
        key: 'username',
        width: 180,
      },
      {
        title: '联系电话',
        dataIndex: 'phone',
        key: 'phone',
        width: 180,
      },{
        title:'申请方式',
        dataIndex:'astype',
        key:"astype",
        width:180,
        render: (text, record) => {
          return <div>{record.astype == 0 ? '退款退货' : '商品更换'}</div>;
        },
      },
      {
        title: '退货原因',
        dataIndex: 'reason',
        key: 'reason',
        width: 300,
      },
      {
        title: '商家操作',
        dataIndex: 'stype',
        key: 'stype',
        render: (text, record) => {
          return <div>{record.stype != 0 ? (record.stype ==-1 ?'拒绝' : '同意'):'审核中'}</div>;
        },
      },
      {
        title: '操作',
        key: 'operation',
        fixed: 'right',
        width: 150,
        render: (text, record) => {
          this.setState({
            RealName_billno: record.billno,
          });
          //操作菜单
          return (
            <div>
            {record.stype != 0 ?
            <a onClick={ev => {this.DetailsModal(ev, record);}} >详情</a>:
            <a onClick={ev => {this.UpdateModal(ev, record);}} >去审核</a>}
            </div>
          );
          
        },
      },
    ];
  }

  //挂载前
  componentDidMount() {
    this.getList();
  }
  //获取全部优惠卷信息
  getList = () => {
    http({
      method: 'get',
      api: 'get_after_sale',
      params: {
        shopcode: this.props.currentUser.billno,
      },
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
        } else {
          message.warn(msg);
        }
        this.setState({
          getLoading: false,
          submitting: false,
          moudelDate: data.list,
        });
      })
      .catch(() => {
        this.setState({ getLoading: false });
      });
    };
    //点击制作优惠卷触发
    gotoAdd=()=>{
      this.setState({
        moudelVisible:true
      })
      this.props.form.setFieldsValue({
        limitnum:1
      })
    }
  //审核
  UpdateModal=(ev, record)=>{
    this.setState({
      moudelVisible:true,
      billno:record.billno,
    })
     this.props.form.setFieldsValue({
      flat:record.stype,
      answer:record.answer
    })
  }
  //详情
  DetailsModal=(ev, record)=>{
    this.setState({
      detailsVisible:true,
      billno:record.billno,
      answer:record.answer
    })
  }
    //对话框点击确定触发
  ModalOk=(e)=>{
    this.setState({ loading: true });
      var billno,admin
      admin=this.props.currentUser.admin
      billno=this.state.billno
      e.preventDefault();
      this.props.form.validateFields((err, values) => {
      if (!err) {
        const params = {
          admin:admin,
          billno:billno,
          flat:values.flat,
          answer:values.answer
        }
        http({
          method: 'get',
          api: 'set_afsale_stus',
          params: {
            ...params
          }
        }).then((result) => {
          const { status, msg, data } = result;
          if (status === '0') {
            message.info('成功');
            this.handleCancel();//点击关闭
            this.getList();
            this.props.form.resetFields();
            this.setState({
              moudelVisible :false
            })
          } else {
            message.info(msg);
          }this.setState( { 
            submitting: false,
            modalVisible: false, });
           }).catch((error) => {
            message.info("错误");
            this.setState({ submitting: false });
          });
      }
    });
  }
  //对话窗关闭
  onCancel=()=>{
    this.setState({
      moudelVisible :false,
      detailsVisible:false
    })
  }
  // 关闭并初始化
  handleCancel = async () => {
      this.props.form.resetFields();
      await this.setState({
        billno:''
      });
  }; 
  //下拉触发的函数
  handleChange=async(value)=>{
    await this.setState({
      flat:value
    })
  }
  render() {
    const {moudelVisible,answer,detailsVisible,moudelDate,getLoading, delLoading } = this.state;
    const {
      form: { getFieldDecorator, setFieldsValue, }
    } = this.props;
    return (
      <PageHeaderWrapper>
        <Modal
        width="40%"
        afterClose={this.handleCancel}
        maskClosable={false}//强制渲染
        title={`审核`}
        visible={moudelVisible}
        onCancel={this.onCancel}
        onOk={this.ModalOk}
        >
          <Form layout="vertical">
            <Row gutter={24}>
              <Col>
                <Form.Item label="商家答复">
                  {getFieldDecorator('answer', {
                    rules: [{ required: true, message: '请输入答复' }],
                  })(<Input placeholder="请输入答复" id="answer" name="answer" type="text" />)}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="审核结果">
                  {getFieldDecorator('flat', {
                    rules: [{ required: true, message: '请选择审核结果' }],
                  })(
                    <Select
                    onChange={this.handleChange}
                    >
                      <Select.Option value="-1">拒绝</Select.Option>
                      <Select.Option value="1">同意</Select.Option>
                    </Select>
                  )}
                </Form.Item>
              </Col>
            </Row>
          </Form>
        </Modal>
        <Modal
        width="40%"
        afterClose={this.handleCancel}
        maskClosable={false}//强制渲染
        tit le={`详情`}
        visible={detailsVisible}
        onCancel={this.onCancel}
        footer={[
          <Button type="primary" key="back" onClick={this.onCancel}>关闭</Button>
        ]}
        >
        <p><span>处理回复：</span><span className="content">{moudelDate.flat==1?"同意":"拒绝"}</span></p>
        <p><span>回复备注：</span><span className="content">{answer}</span></p>
        </Modal>
        <Table
          rowKey={record=>record.id}
          scroll={{ x: 1500, y: 600 }} //高
          dataSource={moudelDate} //数据来源
          columns={this.columns} //每行显示
          pagination={{
            pageSize: 15,
            defaultCurrent: 1,            
          }}
        />
      </PageHeaderWrapper>
    );
  }
}
export default AfterService;
