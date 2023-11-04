<template>
  <div class="app-container">
    <div class="sidebar">
      <div class="sidebar-item-list">
        <router-link v-for="(link, index) in sidebarLinks" :to="link.path" class="sidebar-item"
          :class="{ active: isActive(link.path), selected: index === activeIndex }" :key="link.path">
          {{ link.label }}
        </router-link>
      </div>
    </div>
    <div class="content">
      <router-view></router-view>
    </div>
  </div>
</template>


<style scoped>
.app-container {
  display: flex;
  height: 100vh;
}

.sidebar {
  width: 20%;
  background-color: #333;
  color: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.sidebar-item-list {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
}

.sidebar-item {
  color: #fff;
  text-decoration: none;
  font-size: 18px;
  margin: 10px;
  padding: 10px 20px;
  border-radius: 5px;
  transition: background-color 0.3s ease;
}

.sidebar-item:hover {
  text-decoration: underline;
  background-color: rgba(255, 255, 255, 0.1);
}

.sidebar-item.active {
  background-color: #555;
}

.content {
  flex-grow: 1;
  padding: 20px;
  background-color: #f0f0f0;
}
</style>

<script>
export default {
  data() {
    return {
      activeIndex: 0,
      sidebarLinks: [
        { path: '/device-list', label: 'Device list' },
        { path: '/pair-device', label: 'Pair a Device' },
      ],
    };
  },
  methods: {
    isActive(route) {
      return this.$route.path === route;
    },
    handleArrowUp() {
      this.activeIndex = Math.max(this.activeIndex - 1, 0);
      this.navigateToLink(this.activeIndex);
    },
    handleArrowDown() {
      this.activeIndex = Math.min(this.activeIndex + 1, this.sidebarLinks.length - 1);
      this.navigateToLink(this.activeIndex);
    },
    navigateToLink(index) {
      this.$router.push(this.sidebarLinks[index].path);
    },
    handleKeyDown(event) {
      switch (event.key) {
        case 'ArrowUp':
          this.handleArrowUp();
          break;
        case 'ArrowDown':
          this.handleArrowDown();
          break;
        default:
          break;
      }
    },
  },
  mounted() {
    window.addEventListener('keydown', this.handleKeyDown);
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeyDown);
  },
};
</script>
