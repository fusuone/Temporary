## 使用 normal

- 1. 
```
<AvatarPicker
  initialValue="https://json.kassor.cn/team/headers/Img20171010111054.jpg"
  onChange={value => this.avatar = value}
/>
```

## 使用 @Form.create()

- 1. 设置 initialValue
```
<FormItem label="头像">
  {getFieldDecorator('avatar', {
    initialValue: "https://json.kassor.cn/team/headers/Img20171010111054.jpg",
    rules: [{ required: true, message: '请选择一张头像' }]
  })(
    <AvatarPicker />
  )}
</FormItem>
```

- 2. 通过 setFieldsValue 设置
this.props.form.setFieldsValue({
  avatar: 'http://192.168.3.71:7071/team/images/20181030105059_2224.jpg'
});