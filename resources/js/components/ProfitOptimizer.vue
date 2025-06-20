<template>
    <div class="app-container">
        <!-- Your full HTML here -->
        <div class="header">
                <h1><i class="fas fa-chart-line"></i> Specifi Profit Optimiser</h1>
                <p>AI-powered profitability analysis for AV dealers</p>
            </div>
            <div class="main-content">
                <!-- Project Configuration -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-cog"></i>
                        Project Configuration
                    </h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Labor Cost per Hour (£)</label>
                            <input type="number" v-model="config.laborRate" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label>Estimated Labor Hours</label>
                            <input type="number" v-model="config.laborHours" step="0.1" min="0">
                        </div>
                        <div class="form-group">
                            <label>Fixed Overheads (£)</label>
                            <input type="number" v-model="config.overheads" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label>Target Profit Margin (%)</label>
                            <input type="number" v-model="config.targetMargin" step="0.1" min="0" max="100">
                        </div>
                    </div>
                </div>

                <!-- Products List -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-boxes"></i>
                        Products & Services
                    </h2>
                    
                    <button @click="loadSampleProducts" class="btn btn-primary">
                        <i class="fas fa-download"></i>
                        Load Sample Bill of Materials
                    </button>

                    <table class="products-table" v-if="products.length > 0">
                        <thead>
                            <tr>
                                <th>Product/Service</th>
                                <th>Cost Price (£)</th>
                                <th>Sell Price (£)</th>
                                <th>Quantity</th>
                                <th>Margin (%)</th>
                                <th>Total Profit (£)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(product, index) in products" :key="index" 
                                :class="{ 'low-margin': getProductMargin(product) < 20 }">
                                <td>
                                    <input v-model="product.name" style="border: none; background: transparent; width: 100%;">
                                </td>
                                <td>
                                    <input type="number" v-model="product.cost" step="0.01" min="0" 
                                           style="border: none; background: transparent; width: 80px;">
                                </td>
                                <td>
                                    <input type="number" v-model="product.sellPrice" step="0.01" min="0"
                                           style="border: none; background: transparent; width: 80px;">
                                </td>
                                <td>
                                    <input type="number" v-model="product.quantity" step="1" min="1"
                                           style="border: none; background: transparent; width: 60px;">
                                </td>
                                <td>{{ getProductMargin(product).toFixed(1) }}%</td>
                                <td>£{{ getProductProfit(product).toFixed(2) }}</td>
                                <td>
                                    <button @click="removeProduct(index)" class="btn btn-add" style="padding: 5px 10px; font-size: 0.8rem;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <button @click="addProduct" class="btn btn-add">
                        <i class="fas fa-plus"></i>
                        Add Product/Service
                    </button>
                </div>

                <!-- Analysis Button -->
                <div style="text-align: center; margin: 30px 0;">
                    <button @click="analyzeProfit" class="btn btn-success" :disabled="isAnalyzing" style="font-size: 1.2rem; padding: 15px 40px;">
                        <i class="fas fa-calculator" v-if="!isAnalyzing"></i>
                        <div class="spinner" v-if="isAnalyzing" style="width: 20px; height: 20px; margin: 0 10px 0 0;"></div>
                        {{ isAnalyzing ? 'Analyzing...' : 'Analyze Profitability' }}
                    </button>
                </div>

                <!-- Results Section -->
                <div v-if="results" class="results-section">
                    <!-- Profitability Summary -->
                    <div class="results-card">
                        <h3 class="section-title">
                            <i class="fas fa-chart-pie"></i>
                            Profitability Analysis
                        </h3>
                        
                        <div class="profit-indicator" :class="getProfitIndicatorClass()">
                            <i :class="getProfitIcon()"></i>
                            {{ getProfitStatus() }}
                        </div>

                        <div class="summary-item">
                            <span>Total Revenue:</span>
                            <span>£{{ results.totalRevenue.toFixed(2) }}</span>
                        </div>
                        <div class="summary-item">
                            <span>Total Costs:</span>
                            <span>£{{ results.totalCosts.toFixed(2) }}</span>
                        </div>
                        <div class="summary-item">
                            <span>Labor Costs:</span>
                            <span>£{{ results.laborCosts.toFixed(2) }}</span>
                        </div>
                        <div class="summary-item">
                            <span>Overheads:</span>
                            <span>£{{ config.overheads }}</span>
                        </div>
                        <div class="summary-item">
                            <span>Gross Profit:</span>
                            <span>£{{ results.grossProfit.toFixed(2) }}</span>
                        </div>
                        <div class="summary-item">
                            <span>Net Profit:</span>
                            <span>£{{ results.netProfit.toFixed(2) }}</span>
                        </div>
                        <div class="summary-item">
                            <span>Profit Margin:</span>
                            <span>{{ results.profitMargin.toFixed(2) }}%</span>
                        </div>
                    </div>

                    <!-- AI Suggestions -->
                    <div class="results-card">
                        <h3 class="section-title">
                            <i class="fas fa-robot"></i>
                            AI Recommendations
                        </h3>
                        
                        <div class="ai-suggestions">
                            <div v-for="suggestion in aiSuggestions" :key="suggestion.id" class="suggestion-item">
                                <h4 style="color: #0ea5e9; margin-bottom: 8px;">
                                    <i :class="suggestion.icon"></i>
                                    {{ suggestion.title }}
                                </h4>
                                <p>{{ suggestion.description }}</p>
                                <div v-if="suggestion.impact" style="margin-top: 8px; font-size: 0.9rem; color: #059669;">
                                    <strong>Potential Impact:</strong> {{ suggestion.impact }}
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 8px; border-left: 4px solid #8b5cf6;">
                            <h4 style="color: #8b5cf6; margin-bottom: 10px;">
                                <i class="fas fa-lightbulb"></i>
                                Executive Summary
                            </h4>
                            <p style="line-height: 1.6;">{{ executiveSummary }}</p>
                        </div>
                    </div>
                </div>

                <!-- Export Section -->
                <!-- <div v-if="results" class="export-section">
                    <h3 style="margin-bottom: 15px;">Export Options</h3>
                    <button @click="exportToPDF" class="btn btn-primary" style="margin-right: 10px;">
                        <i class="fas fa-file-pdf"></i>
                        Export to PDF
                    </button>
                    <button @click="saveQuote" class="btn btn-success">
                        <i class="fas fa-save"></i>
                        Save Quote
                    </button>
                </div> -->
            </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// Reactive state
const config = ref({
  laborRate: 45.0,
  laborHours: 8.0,
  overheads: 500.0,
  targetMargin: 25.0
});

const products = ref([]);
const results = ref(null);
const aiSuggestions = ref([]);
const executiveSummary = ref('');
const isAnalyzing = ref(false);

// Sample data
const sampleProducts = [
  { name: 'Samsung 75" 4K Display QM75R', cost: 1850, sellPrice: 2590, quantity: 2 },
  { name: 'Crestron CP4N Control Processor', cost: 3200, sellPrice: 4480, quantity: 1 },
  { name: 'Shure MXA310 Ceiling Array Microphone', cost: 750, sellPrice: 1050, quantity: 4 },
  { name: 'QSC K12.2 Active Speaker', cost: 580, sellPrice: 812, quantity: 6 },
  { name: 'Extron DTP CrossPoint 84 Matrix Switcher', cost: 2100, sellPrice: 2940, quantity: 1 },
  { name: 'Logitech Rally Camera System', cost: 890, sellPrice: 1246, quantity: 2 },
  { name: 'Cable Management & Installation', cost: 150, sellPrice: 450, quantity: 1 },
  { name: 'System Programming & Configuration', cost: 800, sellPrice: 1600, quantity: 1 },
  { name: 'User Training Session', cost: 120, sellPrice: 300, quantity: 2 }
];

// Methods
const loadSampleProducts = () => {
  products.value = [...sampleProducts];
};

const addProduct = () => {
  products.value.push({
    name: 'New Product/Service',
    cost: 0,
    sellPrice: 0,
    quantity: 1
  });
};

const removeProduct = (index) => {
  products.value.splice(index, 1);
};

const getProductMargin = (product) => {
  if (product.sellPrice <= 0) return 0;
  return ((product.sellPrice - product.cost) / product.sellPrice) * 100;
};

const getProductProfit = (product) => {
  return (product.sellPrice - product.cost) * product.quantity;
};

const analyzeProfit = async () => {
  if (products.value.length === 0) {
    alert('Please add some products or load the sample bill of materials first.');
    return;
  }

  isAnalyzing.value = true;
  await new Promise(resolve => setTimeout(resolve, 2000));

  const totalRevenue = products.value.reduce((sum, p) => sum + (p.sellPrice * p.quantity), 0);
  const totalCosts = products.value.reduce((sum, p) => sum + (p.cost * p.quantity), 0);
  const laborCosts = config.value.laborRate * config.value.laborHours;
  const grossProfit = totalRevenue - totalCosts;
  const netProfit = grossProfit - laborCosts - config.value.overheads;
  const profitMargin = totalRevenue > 0 ? (netProfit / totalRevenue) * 100 : 0;

  results.value = {
    totalRevenue,
    totalCosts,
    laborCosts,
    grossProfit,
    netProfit,
    profitMargin
  };

  await generateAISuggestions();
  isAnalyzing.value = false;
};

const generateAISuggestions = async () => {
  const suggestions = [];
  const margin = results.value.profitMargin;

  const lowMarginProducts = products.value.filter(p => getProductMargin(p) < 20);
  if (lowMarginProducts.length > 0) {
    suggestions.push({
      id: 1,
      icon: 'fas fa-exclamation-triangle',
      title: 'Low Margin Products Detected',
      description: `${lowMarginProducts.length} product(s) have margins below 20%.`,
      impact: `Potential £${(lowMarginProducts.reduce((sum, p) => sum + (p.sellPrice * p.quantity * 0.05), 0)).toFixed(2)} additional profit`
    });
  }

  if (config.value.laborHours > 12) {
    suggestions.push({
      id: 2,
      icon: 'fas fa-clock',
      title: 'High Labor Hours',
      description: 'Consider breaking this project into phases or assigning more team members.',
      impact: 'Improved delivery'
    });
  }

  if (margin < config.value.targetMargin) {
    const shortfall = config.value.targetMargin - margin;
    const additionalRevenue = (results.value.totalRevenue * shortfall) / 100;
    suggestions.push({
      id: 3,
      icon: 'fas fa-arrow-up',
      title: 'Margin Enhancement Opportunity',
      description: `To reach your ${config.value.targetMargin}% margin, increase revenue by £${additionalRevenue.toFixed(2)}.`,
      impact: `£${additionalRevenue.toFixed(2)} extra needed`
    });
  }

  if (products.value.length > 5) {
    suggestions.push({
      id: 4,
      icon: 'fas fa-lightbulb',
      title: 'Bundle Optimization',
      description: 'Consider bundling products for better value.',
      impact: 'Higher order value'
    });
  }

  aiSuggestions.value = suggestions;

  // Executive summary
  let summary = '';
  if (margin >= config.value.targetMargin) {
    summary = `Strong profitability at ${margin.toFixed(1)}% margin. Meets commercial targets.`;
  } else if (margin >= 15) {
    summary = `Moderate margin at ${margin.toFixed(1)}%. Consider optimizing further.`;
  } else {
    summary = `Low profitability at ${margin.toFixed(1)}%. Recommend pricing review.`;
  }

  executiveSummary.value = summary;
};

const getProfitIndicatorClass = () => {
  if (!results.value) return '';
  const margin = results.value.profitMargin;
  if (margin >= config.value.targetMargin) return 'profit-green';
  if (margin >= 15) return 'profit-amber';
  return 'profit-red';
};

const getProfitIcon = () => {
  if (!results.value) return '';
  const margin = results.value.profitMargin;
  if (margin >= config.value.targetMargin) return 'fas fa-check-circle';
  if (margin >= 15) return 'fas fa-exclamation-triangle';
  return 'fas fa-times-circle';
};

const getProfitStatus = () => {
  if (!results.value) return '';
  const margin = results.value.profitMargin;
  if (margin >= config.value.targetMargin) return 'Excellent Profitability';
  if (margin >= 15) return 'Moderate Profitability';
  return 'Low Profitability - Review Required';
};

const exportToPDF = () => {
  alert('PDF export would connect to Laravel backend for generation.');
};

const saveQuote = () => {
  alert('Quote saved! This would POST to Laravel backend.');
};

// Initialize with sample data
onMounted(() => {
  loadSampleProducts();
});
</script>

<style scoped>
.app-container {
  padding: 20px;
}
/* Add more styles here as needed */
</style>
