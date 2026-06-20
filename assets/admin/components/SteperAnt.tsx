import { Button, Input } from 'antd'
import { MinusOutlined, PlusOutlined } from '@ant-design/icons';
import React, { useEffect, useState } from 'react'

export default function Steper(props: {
    min?: number,
    max?: number,
    onChange?: any,
    step?: number,
    defaultValue?: number,
    style?: any,
    disabled?: boolean,
    value?: number,
} = {
    min: 0,
    max: 99999,
    onChange: () => { },
    step: 1,
    defaultValue: 0,
    value: 0,
    style: {},
    disabled: false
}) {
    props = {
        min: 0,
        max: 99999,
        onChange: () => { },
        disabled: false,
        step: 1,
        defaultValue: 0,
        style: {},
        value: 0,
        ...props,
    };

    const [state, setstate] = useState(props.defaultValue || 0);
    const [edit, setedit] = useState(false)

    useEffect(() => {
        // @ts-ignore
        if (props.value > 0) {
            // @ts-ignore
            setstate(props.value)
        }
    }, [props.value])
    return (
        <div className="steper" style={props.style}>

            <Button onClick={() => {
                const tmp = (typeof state == 'string' ? ( isNaN(parseInt(state)) ? 0 : parseInt(state)) : state) - 1;
                setstate(tmp);
                props.onChange(tmp)
            }}
            // @ts-ignore
            disabled={state <= props.min || props.disabled} icon={<MinusOutlined />}></Button>
            <span style={{ display:'inline-block', width: 46, textAlign: 'center', color: '#3c3c3c' }} className="number">
                <Input onChange={(e: any) => {
                    let tmp = parseInt(e.target.value);
                    if (isNaN(tmp) || tmp < 0 || tmp == null) {
                        tmp = 0;
                    }
                    setstate(tmp);
                    props.onChange(tmp);
                }} width="50px" disabled={props.disabled} style={{ textAlign: 'center', background: 'none', border: 'none'}} value={state} defaultValue={state} />
            </span>

            <Button onClick={() => {
                const tmp = (typeof state == 'string' ? ( isNaN(parseInt(state)) ? 0 : parseInt(state)) : state)  + 1;
                setstate(tmp);
                props.onChange(tmp)
            }}
            // @ts-ignore
            disabled={state >= props.max || props.disabled} icon={<PlusOutlined />}></Button>
        </div>
    );
}
