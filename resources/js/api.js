import axios from "axios";
const axiosPost = async (endpoint, params = null) => {
    return await axios.post("https://desafio-reportei-pedro.codejunior.com.br/api/" + endpoint, params)
}

export { axiosPost }
