import '../bootstrap'
import './styles/login.scss'

import { Alert, Button, Checkbox, Form, Input, Typography, notification } from 'antd'
import { LockOutlined, UserOutlined } from '@ant-design/icons'
import React, { useEffect, useState } from 'react'

import ReactDOM from 'react-dom'
// @ts-ignore
import bgImage from '../styles/images/bg.png'
// @ts-ignore
import logoImage from '../styles/images/logo.png'
import { useRequest } from 'ahooks'

function AdminLogin() {
    const csrf = document.querySelector("meta[name='csrf_token']");
    let csrftoken: any = "";
    const root_url = (window as any).root_admin || '/admin'
    if (csrf) {
        csrftoken = csrf.getAttribute('content');
    }
    const [CsrfToken, setCsrfToken] = useState(csrftoken)
    const [ErrorInfo, setErrorInfo] = useState("")
    const [ErrorDesc, setErrorDesc] = useState("")
    const [Loadding, setLoadding] = useState(false);

    const loginSubmit = useRequest((submit_data: any = {}) => ({
        url: '/api/admin/account/login',
        method: 'POST',
        body: JSON.stringify(submit_data),
        headers: {
            'Content-Type': 'application/json'
        },
    }), {
        manual: true,
        onSuccess: () => notification.success({
            message: '登录成功',
            description: '请稍后，即将跳转到',
            duration: 1,
            onClose: () => {
                setLoadding(false);
                location.href = root_url;
            }
        }),
        onError: () => (notification.error({
            message: '登录失败',
            description: '网络错误，或其他问题',
            duration: 5,
            onClose: () => (setErrorInfo(""), setErrorDesc(""), setLoadding(false))
        }), setErrorInfo("登录失败"), setErrorDesc("网络错误，或其他问题"))
    });

    return (
        <div className="admin-login-layout" style={{ backgroundImage: `url(${bgImage})` }}>
            <div className="admin-login">
                <div className="login-frame">
                    <div className="title">
                        <img src={logoImage} height="40" alt="" />
                        <Typography.Title level={3} title="后台登录" className="title-string">
                            后台系统
                    </Typography.Title>
                    </div>
                    {ErrorInfo !== "" && <div className="error-info" style={{ marginBottom: 20 }}>
                        <Alert type="error" closable description={ErrorDesc} message={ErrorInfo} onClose={() => (setErrorInfo(""), setErrorDesc(""))} />
                    </div>}
                    <div className="forms">
                        <Form labelCol={{ span: 4 }} onFinish={(value: any) => {
                            setLoadding(true);
                            loginSubmit.run({
                                ...value,
                                csrf_token: CsrfToken
                            })
                        }}>
                            <Form.Item style={{ textAlign: 'left' }} name="account_name" required rules={[{
                                required: true,
                                message: "请输入用户名"
                            }, {
                                min: 5,
                                message: '未知用户名'
                            }]}>
                                <Input size="large" placeholder="请输入用户名" prefix={<span><UserOutlined /></span>} />
                            </Form.Item>
                            <Form.Item style={{ marginBottom: 18, textAlign: 'left' }} name="account_password" rules={[{
                                required: true,
                                message: "请输入密码"
                            }, {
                                min: 5,
                                message: '密码长度错误'
                            }]}>
                                <Input.Password size="large" placeholder="请输入密码" prefix={<span><LockOutlined /></span>} />
                            </Form.Item>

                            <Form.Item style={{ textAlign: 'left', marginBottom: 0 }}>
                                <Form.Item style={{ display: 'inline-block', marginBottom: 18 }} name="remember" valuePropName="checked">
                                    <Checkbox style={{ fontWeight: 600, color: '#7b7b7b' }}>记住用户</Checkbox>
                                </Form.Item>
                                <Form.Item style={{ display: 'inline-block', marginBottom: 18, float: 'right' }}>
                                    <a href="/admin/forget">忘记密码?</a>
                                </Form.Item>
                            </Form.Item>
                            <Button loading={Loadding} block htmlType="submit" type="primary" size="large">登录</Button>
                        </Form>
                    </div>
                </div>
            </div>
        </div>
    );
}

ReactDOM.render(<AdminLogin />, document.querySelector('#admin'));
