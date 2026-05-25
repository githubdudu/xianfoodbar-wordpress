import 'antd/dist/antd.css';
import '@ant-design/pro-layout/dist/layout.css';

import React from 'react'
import ReactDOM from 'react-dom'
import { UseRequestProvider } from 'ahooks';

export default function BaseApp(element_query: string, componets: any) {

    return ReactDOM.render(
        <UseRequestProvider value={{
        }}>
            {componets}
        </UseRequestProvider>, document.querySelector(element_query));
}