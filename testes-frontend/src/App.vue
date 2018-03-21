<template>
	<div id="app">
		<h3>GET</h3>
		<button @click="getRequest('/members')">Retorna todos os members</button>
		<button class="failed" @click="getRequest('/members/72')">Retorna o member pelo seu memid (72)</button>
		<button @click="getRequest('/gestaourbanasp_consulta_piu_terminais')">Retorna todos os members relacionados a consulta informada (gestaourbanasp_consulta_piu_terminais)</button>

		<h3>POST</h3>
		<button class="failed" @click="postCreateMember">Cria um novo member público de nome 'Teste olar'</button>

		<h4>Resposta API</h4>
		<p v-if='respostaApi'><strong>Primeiro item "memid"</strong>: " {{ respostaApi.data[0] }}</p>
		<p>{{ respostaApi }}</p>
		<hr>

	</div>
</template>

<script>
import axios from 'axios';

export default {
	name: 'app',
	data(){
		return {
			respostaApi: null,
			apiUrlBasename: 'http://localhost/consultas-publicas-backend/testeapi.php'
		}
	},
	methods: {
		getRequest(parameter){
			console.log('getRequest: '+ this.apiUrlBasename + parameter);

			this.axios.get(this.apiUrlBasename + parameter).then((response) => {
				this.respostaApi = response;
			})
		}, 
		postCreateMember(){
			const app = this;
			const url = app.apiUrlBasename +'/members';

			console.log('post: ' + url + " |  { 'name': 'Teste olar', 'public': '1'}")
			
			axios.post(url, {
				'name': "Teste olar", 
				'public': '1' 
			})
			.then(response => {
				console.log(response)
			})
			.catch(e => {
				console.log(e)
			})
		},

	}
}
</script>

<style scoped>
.failed{
	background-color: #ff4141
}
</style>


