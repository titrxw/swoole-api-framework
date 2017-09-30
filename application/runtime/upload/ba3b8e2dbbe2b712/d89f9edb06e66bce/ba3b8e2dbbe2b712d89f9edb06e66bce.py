# -*- coding: utf-8 -*-
import urllib
import urllib2
import ssl
import re
import cookielib
from netTool import NetTool

class Https:
    __url=''
    __data={}
    __headers={}
    __opener=None
    __cookies=None
    __code=200
    __proxyIp = None

    def __init__(self,url,data,installCookie=True):
        if(installCookie):
            self.installCookie()

        self.__url=url
        self.__data=data

    def setHttpProxyIp(self,ip):
        if(ip is not None):
            self.__proxyIp={'http': ip}

    def setHttpsProxyIp(self,ip):
        if(ip is not None):
            self.__proxyIp={'https': ip}

    def setUrl(self,url):
        self.__url=url


    def getUrl(self):
        return self.__url


    def setData(self,data):
        self.__data=data


    def getData(self):
        return self.__data


    def setHeader(self,headers=None):
        if headers is None:
            headers={'User-Agent':'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116'}
        self.__headers=headers


    def getHeaders(self):
        return self.__headers

    
    def makeCookie(self,name,value):
        return cookielib.Cookie(
            version=0,
            name=name,
            value=value,
            port=None,
            port_specified=False,
            domain=NetTool.getDoMain(),
            domain_specified=True,
            domain_initial_dot=False,
            path="/",
            path_specified=True,
            secure=False,
            expires=None,
            discard=False,
            comment=None,
            comment_url=None,
            rest=None
        )
    

    def setCookies(self,cookies):
        self.__cookies.set_cookie(cookies)


    def clearSessionCookie(self):
        self.__cookies.clear_session_cookies()

    
    def clear(self):
        self.__cookies.clear()


    def getCookies(self):
        return self.__cookies
    

    def getFormatCookies(self):
        formatCookies={}
        for item in self.__cookies:
            formatCookies[item.name]=item.value
        return formatCookies

        
    def encodeRequestData(self):
        return  urllib.urlencode(self.getData())


    def installCookie(self):
        if self.__opener is None:
            self.__cookies = cookielib.CookieJar()
            proxy_support= None
            if self.__proxyIp is not None:
                proxy_support = urllib.request.ProxyHandler(self.__proxyIp)
                self.__opener = urllib2.build_opener(urllib2.HTTPCookieProcessor(self.__cookies),proxy_support)
            else:
                self.__opener = urllib2.build_opener(urllib2.HTTPCookieProcessor(self.__cookies))
            urllib2.install_opener(self.__opener)


    def get(self):
        req = urllib2.Request(url=self.getUrl(),headers=self.getHeaders())
        return self.__run(req)


    def post(self):
        req = urllib2.Request(self.getUrl(),data=self.encodeRequestData(),headers=self.getHeaders())
        return self.__run(req)


    def ajax(self):
        headers=self.getHeaders()
        if len(headers)<=0:
            header={
                "Content-Type":"application/x-www-form-urlencoded; charset=UTF-8",
                'X-Requested-With':"XMLHttpRequest",
                "User-Agent":"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116"
            }
        else:
            header=headers
        self.setHeader(header)

        req = urllib2.Request(self.getUrl(),data=self.encodeRequestData(),headers=header)
        return self.__run(req)


    def __run(self,req):
        try:
            ssl._create_default_https_context = ssl._create_unverified_context
            if self.__opener is not None:
                response=self.__opener.open(req)
                self.__code=response.code
                return response.read()
            else:
                context = ssl._create_unverified_context()
                response=urllib2.urlopen(req,context=context)
                self.__code=response.code
                return response.read()
        except Exception,e:
            raise Exception(e.message)


    def getCode(self):
        return self.__code