import { createModel } from 'hox'
import { useState } from 'react';

function useConfig() {

    const ConfigType: {
        Header?: string,
        title?: string,
        sub_title?: string,
        extarContent?: string,
        ShowHeader?: boolean,
        Loadding?: boolean,
    } = {
        Header: '管理后台',
        title: '管理后台',
        sub_title: '管理后台',
        Loadding: false,
        extarContent: '',
        ShowHeader: false,
    };
    const [Config, SetConfig] = useState(ConfigType)
    const [IsMobile, setIsMobile] = useState(false);

    const [TableConfig, setTableConfig] = useState<{
        description?: string,
        sub_title?: string,
        title?: string,
        column?: any[],
        api_list?: any,
        showSelect?: any,
        buttons: any[],
        tool_buttons: any[],
        alert_buttons: any[],
        tableData: {
            title: string,
            content: string,
        }[],
        tableDataTitle: string,
    }>({
        description: "管理",
        sub_title: "管理",
        title: "管理",
        column: [],
        api_list: {
            data: "",
        },
        showSelect: false,
        buttons: [],
        tool_buttons: [],
        alert_buttons: [],
        tableData: [],
        tableDataTitle: "",
    })

    return {
        Config,
        SetConfig,
        TableConfig,
        setTableConfig,
        IsMobile,
        setIsMobile,
    };
}

export default createModel(useConfig);