/**
 * 格式化小数
 */
function fomatFloat(number, isstring){
    var f  = parseFloat(number);
    if(isstring && typeof (isstring) != 'undefined'){
        if(isNaN(number) || (number == '') || (number == null) || (number == 0)) return '0.00';
        return (Math.round(f*100)/100).toFixed(2);
    }else{
        if(isNaN(number) || (number == '') || (number == null)) return parseInt(0);
        return Math.round(f*100)/100;
    }
}

/**
 * 格式化整数
 */
function fomatInt(number){
    if(isNaN(number) || (number == '') || (number == null)){
        return parseInt(0);
    }
    var f  = parseInt(number);
    return f;
}

/**
 * 判断是否为整数
 * @param val
 * @returns {boolean}
 */
function isInteger(val){
    return val%1 === 0;
}
