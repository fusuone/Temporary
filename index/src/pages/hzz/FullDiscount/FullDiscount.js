import React, { PureComponent } from 'react';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { Table,Modal,message,InputNumber,Col, Form,Button ,Row ,Input} from 'antd';
import http from '@/utils/http';
import { connect } from 'dva';

@connect(({ user }) => ({
  currentUser: user.currentUser,
}))
@Form.create()
class FullDiscount extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      getLoading: true,
      moudelVisible:false,
      moudelDate: [],
      billno:'',
      StatusVisible:'0',//编辑还是新增
    };
    this.columns = [
      {
        title: '编号',
        dataIndex: 'billno',
        key: 'billno',
        width: 200,
      },
      {
        title: '标题',
        dataIndex: 'title',
        key: 'title',
        width: 200,
      },
      {
        title: '优惠金额',
        dataIndex: 'rmb',
        key: 'rmb',
        width: 200,
      },
      {
        title: '使用金额',
        dataIndex: 'litrmb',
        key: 'litrmb',
        width: 200,
      },{
        title:'当前状态',
        dataIndex:'status',
        key:"status",
        render: (text, record) => {
          return <div>{record.status == -1 ? '下架' : '上架'}</div>;
        },

      },
      {
        title: '操作',
        key: 'operation',
        fixed: 'right',
        width: 200,
        render: (text, record) => {
          this.setState({
            RealName_billno: record.billno,
          });
          //操作菜单
          return (
            <div>
              <a style={{marginRight:40}}
                onClick={ev => {this.UpdateModal(ev, record);}} >
                更改  
              </a>
              <a onClick={(ev) => this.delTrack(ev,text)}>
                  {record.status=="-1"?`上架`:`下架`}
              </a>
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
    this.setState({
      getLoading: true,
    })
    http({
      method: 'get',
      api: 'get_mall_disc',
      params: {
        billno:this.props.currentUser.billno,
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
  //编辑
  UpdateModal=(ev, record)=>{
    this.setState({
      StatusVisible:'1',
      moudelVisible:true,
      billno:record.billno,
    })
    this.props.form.setFieldsValue({
      title:record.title,
      rmb :record.rmb,
      litrmb:record.litrmb,
    })
  }
  delTrack=(ev, record)=>{
    const status=record.status
    http({
      method: 'get',
      api: 'set_mdisc_staus',
      params: { 
        flat: status=='0'? '-1':'0',
        billno: record.billno,
      },
    }).then((result) => {
      const { status, msg, data } = result;
      if (status == '0') {
        message.info('修改成功');
        this.getList();
      } else {
        message.info(msg);
      }
    }).catch(() => {
      message.info('操作失败');
    });
  }
    //对话框点击确定触发
  ModalOk=(e)=>{
    this.setState({ loading: true });
      var admincode,billno,admin
      billno=this.state.billno
      e.preventDefault();
      this.props.form.validateFields((err, values) => {
      if (!err) {
        const data = {
          billno:billno,
          title:values.title,
          rmb :values.rmb,
          litrmb:values.litrmb,
          admincode:this.props.currentUser.admincode
        }
        http({
          method: 'post',
          api: 'set_mall_disc',
          data: {
            ...data
          }
        }).then((result) => {
          const { status, msg, data } = result;
          if (status === '0') {
            message.info('成功');
            this.handleCancel();//点击关闭
            this.getList();
            this.setState({
              moudelVisible :false
            })
          } else {
            message.info(msg);
          }this.setState( { 
            submitting: false,
            loading:false,
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
      moudelVisible :false
    })
    this.getList();
  }
  // 关闭并初始化
  handleCancel = async () => {
      this.props.form.resetFields();
      await this.setState({
        StatusVisible:'0',//区分编辑和新增
        billno:'',
      });
  };
  render() {
    const {StatusVisible, moudelVisible,moudelDate, delLoading } = this.state;
    const {
      form: { getFieldDecorator, setFieldsValue, }
    } = this.props;
    return (
      <PageHeaderWrapper>
        <Button type="primary" 
        onClick={this.gotoAdd}
        >
          生成满减优惠
        </Button>
        <Modal
        width="40%"
        afterClose={this.handleCancel}
        maskClosable={false}//强制渲染
        title={(StatusVisible=='1' ? `更改`:`新增`)
          +`满减优惠`
        }
        visible={moudelVisible}
        onCancel={this.onCancel}
        onOk={this.ModalOk}
        >
          <Form layout="vertical">
            <Row gutter={24}>
              <Col>
                <Form.Item label="说明规则">
                  {getFieldDecorator('title', {
                    rules: [{ required: true, message: '请输入商品标题' }],
                  })(<Input placeholder="请输入商品标题" id="title" name="title" type="text" />)}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="优惠送的金额">
                  {getFieldDecorator('rmb', {
                    rules: [{ required: true, message: '请输入大于0的纯数字' }],
                  })(<InputNumber style={{ width:"36vw" }} min={1}step={0.1} />,)}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="限定金额">
                  {getFieldDecorator('litrmb', {
                    rules: [{ required: true, message: '请输入消费额' }],
                  })(<InputNumber style={{ width:"36vw"}} min={0}step={0.1} />,)}
                </Form.Item>
              </Col> 
            </Row>
          </Form>
        </Modal>
        <Table
          rowKey={record=>record.id}
          scroll={{ x: 1400, y: 600 }} //高
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
export default FullDiscount;
