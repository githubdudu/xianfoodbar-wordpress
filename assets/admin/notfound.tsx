import '../styles/notfound.scss'

import { Button, Table } from 'antd'
import React, { useState } from 'react'

// @ts-ignore
import not404 from '../styles/images/404.png'
import useConfig from './components/useConfig'

function AdminNotFound() {
    const { Config, SetConfig } = useConfig();

    SetConfig({
        ...Config,
        ShowHeader: true,
        title: '没有找到页面，请确认链接是否正确？'
    })
    return (

        <div className="centerShow">
            <div className="item">
                <img src={not404} width={500} alt="" />
            </div>
            <div className="item">
                <span>没有找到页面，请确认链接是否正确？</span>
            </div>
            <div className="item">
                <Button type="primary" shape="round" onClick={() => window.history.back()}>点击返回</Button>
            </div>
        </div>
    );
}
// ReactDOM.render(<AdminNotFound />, document.querySelector("#admin404"));
export default AdminNotFound;
