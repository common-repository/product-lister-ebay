  function initFreshChat() {
    window.fcWidget.init({
      	 token: "3fc487b1-e083-43e6-9a8d-8a4d42f188fc",
	 host: "https://cedcommercechatsupport.freshchat.com",
	 widgetUuid: "183794f3-493c-4871-ac94-13ab9e94daea"
    });
  }
  function initialize(i,t){var e;i.getElementById(t)?
  initFreshChat():((e=i.createElement("script")).id=t,e.async=!0,
  e.src="https://cedcommercechatsupport.freshchat.com/js/widget.js",e.onload=initFreshChat,i.head.appendChild(e))}
  function initiateCall(){initialize(document,"Freshchat-js-sdk")}
  window.addEventListener?window.addEventListener("load",initiateCall,!1):
  window.attachEvent("load",initiateCall,!1);
