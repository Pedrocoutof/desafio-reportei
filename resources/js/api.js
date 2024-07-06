import axios from "axios";
const axiosPost = async (endpoint, params = null) => {
    return await axios.post("http://127.0.0.1:8000/api/" + endpoint, params)
}

export { axiosPost }
