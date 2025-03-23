<template>
  <nav v-if="totalPages > 1" class="mt-3">
    <ul class="pagination">
      <!-- Previous Button -->
      <li class="page-item" :class="{ disabled: currentPage === 1 }">
        <button class="page-link" @click="changePage(currentPage - 1)">
          Previous
        </button>
      </li>

      <!-- Page Numbers -->
      <li v-for="page in displayedPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
        <button v-if="page !== '...'" class="page-link" @click="changePage(page)">
          {{ page }}
        </button>
        <span v-else class="page-link disabled">...</span>
      </li>

      <!-- Next Button -->
      <li class="page-item" :class="{ disabled: currentPage === totalPages }">
        <button class="page-link" @click="changePage(currentPage + 1)">
          Next
        </button>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  props: {
    totalPages: Number,
    startPage: Number,
  },

  data() {
    return {
      currentPage: this.startPage,
    };
  },

  watch: {
    startPage: function (page) {
      if (page !== this.currentPage) {
        this.currentPage = page;
      }
    },
  },

  computed: {
    displayedPages() {
      let pages = [];

      if (this.totalPages <= 7) {
        for (let i = 1; i <= this.totalPages; i++) {
          pages.push(i);
        }
      } else {
        pages.push(1);
        if (this.currentPage > 4) pages.push("...");

        let start = Math.max(2, this.currentPage - 1);
        let end = Math.min(this.totalPages - 1, this.currentPage + 1);

        for (let i = start; i <= end; i++) {
          pages.push(i);
        }

        if (this.currentPage < this.totalPages - 3) pages.push("...");
        pages.push(this.totalPages);
      }

      return pages;
    },
  },

  methods: {
    changePage(page) {
      if (page !== "..." && page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.$emit("page-changed", page);
      }
    },
  },
};
</script>
