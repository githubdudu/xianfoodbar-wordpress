import './demo.scss'

import { Button, Col, Input, Row } from 'antd';
import React, { useState } from 'react'

import { useRequest } from 'ahooks';

export default function AddCountBtns(props: {
    onChange: any
}) {
    const number = [1, 2, 3, 4, 5, 6, 7, 8, 9, ' ', 0];
    const [orderNum, setOrderNum] = useState("");

    const getMenu = useRequest((oid: any) => `/api/menu/get/${oid}`, {
        manual: true,
        fetchKey: (oid: any) => `get${oid}`,
        onSuccess: (data) => props.onChange(data),
    })

    return (
        <div className="addCountBtns">
            <div className="center">
                <div className="input">
                    <Row gutter={10}>
                        <Col span="16">
                            <Input
                                placeholder="菜品编号，请使用下方的按键输入"
                                value={orderNum}
                                size="large"
                                // onPressEnter={(e) => {
                                //     // @ts-ignore
                                //     getMenu.run(orderNum);
                                //     setOrderNum("");
                                //     return false;
                                // }}
                                onChange={(e) => {
                                    if (e.target.value.length > 0) {
                                        // @ts-ignore
                                        setOrderNum(parseInt(e.target.value || "",),);
                                    } else {
                                        setOrderNum("");
                                    }
                                }}
                            />
                        </Col>
                        <Col span="8">
                            <Button
                                size="large"
                                onClick={() => {
                                    // @ts-ignore
                                    getMenu.run(orderNum);
                                    setOrderNum("");
                                }}
                                type="primary"
                            >确定</Button>
                        </Col>
                    </Row>
                </div>
            </div>
            <div className="buttons">
                <Row className="button_row">
                    {number.map((v, key) => (
                        <Col
                            key={key}
                            className="button_item"
                            span="8"
                        >
                            <Button
                                onClick={() =>
                                    setOrderNum(
                                        orderNum +
                                        "" +
                                        v.toString().trim(),
                                    )
                                }
                                style={{
                                    fontSize: 30,
                                    fontWeight: 'bold',
                                    color: '#636363'
                                }}
                                className="button_button"
                            >
                                {v}
                            </Button>
                        </Col>
                    ))}
                    <Col
                        className="button_item"
                        span="8"
                    >
                        <Button
                            danger
                            onClick={() => setOrderNum("")}
                            type="primary"
                            style={{
                                fontSize: 30,
                                fontWeight: 'bold',
                            }}
                            className="button_button"
                        >
                            清除
                        </Button>
                    </Col>
                </Row>
            </div>
        </div >
    );
}
