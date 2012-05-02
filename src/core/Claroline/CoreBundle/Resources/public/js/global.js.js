
String.prototype.addSlashes = function()
{return this.replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');};
 
String.prototype.stripSlashes = function()
{return this.replace(/\\(.?)/g, function (s, n1){
        switch (n1){
            case '\\':return '\\';
            case '0':return '\u0000'
            case '':return '';
            default:return n1;
            }
        }
    );
};