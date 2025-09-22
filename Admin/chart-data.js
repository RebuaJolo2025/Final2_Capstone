// Revenue trend data
const revenueData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
    datasets: [
        {
            label: 'Revenue',
            data: [45000, 52000, 48000, 61000, 58000, 67000, 72000, 69000],
            borderColor: 'hsl(217, 91%, 60%)',
            backgroundColor: 'hsla(217, 91%, 60%, 0.1)',
            fill: true,
            tension: 0.4
        },
        {
            label: 'Profit',
            data: [12000, 15600, 14400, 18300, 17400, 20100, 21600, 20700],
            borderColor: 'hsl(142, 76%, 36%)',
            backgroundColor: 'hsla(142, 76%, 36%, 0.1)',
            fill: true,
            tension: 0.4
        }
    ]
};

// Sales by category data
const categoryData = {
    labels: ['Dresses', 'Tops', 'Bottoms', 'Accessories', 'Outerwear'],
    datasets: [{
        data: [35, 25, 20, 12, 8],
        backgroundColor: [
            'hsl(217, 91%, 60%)',
            'hsl(142, 76%, 36%)',
            'hsl(45, 93%, 47%)',
            'hsl(0, 72%, 51%)',
            'hsl(262, 83%, 58%)'
        ],
        borderWidth: 0,
        hoverBorderWidth: 2,
        hoverBorderColor: '#fff'
    }]
};

// Top products data
const topProducts = [
    {
        id: 1,
        name: "Summer Floral Dress",
        category: "Dresses",
        sales: 234,
        revenue: "$14,520",
        change: 12.5,
        trend: "up",
        stock: 45,
    },
    {
        id: 2,
        name: "Casual Denim Jacket",
        category: "Outerwear", 
        sales: 189,
        revenue: "$11,340",
        change: 8.2,
        trend: "up",
        stock: 23,
    },
    {
        id: 3,
        name: "High-Waist Skinny Jeans",
        category: "Bottoms",
        sales: 167,
        revenue: "$10,020",
        change: -3.1,
        trend: "down",
        stock: 67,
    },
    {
        id: 4,
        name: "Silk Blouse - White",
        category: "Tops",
        sales: 156,
        revenue: "$9,360",
        change: 15.8,
        trend: "up",
        stock: 12,
    },
    {
        id: 5,
        name: "Leather Handbag",
        category: "Accessories",
        sales: 134,
        revenue: "$8,040",
        change: 6.3,
        trend: "up",
        stock: 34,
    },
];

// Recent transactions data
const recentTransactions = [
    {
        id: "TXN-001",
        customer: "Sarah Johnson",
        email: "sarah.j@email.com",
        amount: "$156.50",
        status: "completed",
        date: "2 minutes ago",
        items: 3,
    },
    {
        id: "TXN-002", 
        customer: "Michael Chen",
        email: "m.chen@email.com",
        amount: "$89.99",
        status: "processing",
        date: "8 minutes ago",
        items: 1,
    },
    {
        id: "TXN-003",
        customer: "Emily Davis",
        email: "emily.davis@email.com", 
        amount: "$234.75",
        status: "completed",
        date: "15 minutes ago",
        items: 5,
    },
    {
        id: "TXN-004",
        customer: "James Wilson",
        email: "j.wilson@email.com",
        amount: "$67.20",
        status: "failed",
        date: "23 minutes ago",
        items: 2,
    },
    {
        id: "TXN-005",
        customer: "Lisa Anderson",
        email: "lisa.a@email.com",
        amount: "$445.00",
        status: "completed",
        date: "1 hour ago",
        items: 7,
    },
];

// Chart configuration options
const chartOptions = {
    revenue: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 20
                }
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                titleColor: '#1e293b',
                bodyColor: '#475569',
                borderColor: '#e2e8f0',
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: true,
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    color: 'rgba(148, 163, 184, 0.1)'
                },
                ticks: {
                    color: '#64748b'
                }
            },
            y: {
                grid: {
                    color: 'rgba(148, 163, 184, 0.1)'
                },
                ticks: {
                    color: '#64748b',
                    callback: function(value) {
                        return '$' + (value / 1000) + 'k';
                    }
                }
            }
        }
    },
    category: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 20
                }
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                titleColor: '#1e293b',
                bodyColor: '#475569',
                borderColor: '#e2e8f0',
                borderWidth: 1,
                cornerRadius: 8,
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.parsed + '%';
                    }
                }
            }
        }
    }
};