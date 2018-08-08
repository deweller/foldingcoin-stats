function plugin(Vue) {

    // add an instance method
    Vue.prototype.$request = {
        async get(url, config, onError) {
            try {
                // console.log(`get ${url}`, config);
                let response = await window.axios.get(url, config)
                return response.data
            } catch (error) {
                let errorMsg
                if (error.response) {
                    errorMsg = error.response.data.message
                } else {
                    errorMsg = "Unable to complete request"
                }

                if (onError != null) {
                    onError(errorMsg)
                }

                throw error
            }
        }

    }

}

export default plugin;
