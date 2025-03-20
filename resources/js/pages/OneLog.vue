<template>
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h4>Request Log #{{ this.$route.params.id }}</h4>
  </div>
  <div>
    <vue-json-pretty :data="log" :showIcon="true" :theme="RequestLogger.theme"/>
  </div>
</template>

<script>
import VueJsonPretty from 'vue-json-pretty';
import 'vue-json-pretty/lib/styles.css';

export default {
  components: {
    VueJsonPretty,
  },

  data() {
    return {
      log: {},
    };
  },

  mounted() {
    this.loadRequestLog();
  },

  methods: {
    loadRequestLog() {
      this.$http.get(RequestLogger.basePath + '/api/' + this.$route.params.id)
          .then(response => {
            this.log = response.data;
          });
    }
  }
}
</script>
